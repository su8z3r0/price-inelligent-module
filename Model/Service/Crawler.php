<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Service;

use Cyper\PriceIntelligent\Api\CrawlerInterface;
use Cyper\PriceIntelligent\Api\PriceParserInterface;
use Cyper\PriceIntelligent\Api\ProxyRotatorInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
    public function __construct(
        LoggerInterface $logger,
        PriceParserInterface $priceParser,
        ProxyRotatorInterface $proxyRotator,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $logger;
        $this->priceParser = $priceParser;
        $this->proxyRotator = $proxyRotator;
        $this->scopeConfig = $scopeConfig;
    }

    public function scrapeProduct(array $config, string $url): array
    {
        $maxRetries = (int) $this->scopeConfig->getValue(self::CONFIG_PATH_MAX_RETRIES) ?: 3;
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxRetries) {
            $ch = null;
            try {
                // Get proxy if enabled
                $proxy = $this->proxyRotator->getNextProxy();
                
                $ch = curl_init();
                
                // Generic Options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Prevent infinite loops
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                
                // Connection hygiene
                curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
                curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
                
                // Headers & User Agent
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
                // Explicitly disable Expect header which can cause issues
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Expect:']);

                // Note: We are deliberately NOT setting CURLOPT_COOKIEJAR/COOKIEFILE
                // to prevent cookie accumulation loops which cause HTTP 400.
                
                // Proxy Configuration
                if ($proxy) {
                    curl_setopt($ch, CURLOPT_PROXY, $proxy['url']);
                    if (!empty($proxy['username']) && !empty($proxy['password'])) {
                        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy['username'] . ':' . $proxy['password']);
                    }
                    $this->logger->info('Scraping with proxy: ' . $proxy['url']);
                }
                
                $html = curl_exec($ch);
                $error = curl_error($ch);
                $errno = curl_errno($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                
                curl_close($ch);
                $ch = null;

                if ($errno || $html === false) {
                    throw new \RuntimeException('CURL Error: ' . $error);
                }

                if ($httpCode >= 400) {
                    throw new \RuntimeException('HTTP Error: ' . $httpCode);
                }
                
                if (empty($html)) {
                    throw new \RuntimeException('Empty response from server');
                }
                
                $crawler = new DomCrawler($html);

                return [
                    'product_url' => $url,
                    'ean' => $this->extractEan($crawler, $config),
                    'product_title' => trim($this->extractTitle($crawler, $config)),
                    'sale_price' => $this->extractPrice($crawler, $config),
                    'scraped_at' => date('Y-m-d H:i:s'),
                ];
                
            } catch (\Exception $e) {
                if ($ch) {
                    curl_close($ch);
                }
                $lastException = $e;
                $attempt++;
                
                // Mark proxy as failed if used
                if ($proxy) {
                    $this->proxyRotator->markProxyAsFailed($proxy['url']);
                    $this->logger->warning('Proxy failed, attempt ' . $attempt . '/' . $maxRetries, [
                        'proxy' => $proxy['url'],
                        'error' => $e->getMessage()
                    ]);
                } else {
                    $this->logger->warning('Scraping failed (no proxy), attempt ' . $attempt . '/' . $maxRetries, [
                        'url' => $url,
                        'error' => $e->getMessage()
                    ]);
                }
                
                // Sleep before retry
                if ($attempt < $maxRetries) {
                    sleep(2);
                }
            }
        }
        
        // All retries failed
        $this->logger->error('Scraping failed after ' . $maxRetries . ' attempts', [
            'url' => $url,
            'last_error' => $lastException->getMessage()
        ]);
        
        throw $lastException;
    }

    protected function extractEan(DomCrawler $crawler, array $config): ?string
    {
        $method = $config['selectors']['ean']['method'] ?? 'json_ld';

        switch ($method) {
            case 'json_ld':
                return $this->extractEanFromJsonLd($crawler, $config['selectors']['ean']['field'] ?? 'gtin13');
            case 'meta':
                return $this->extractEanFromMeta($crawler);
            case 'data_attribute':
                return $this->extractEanFromDataAttribute($crawler, $config['selectors']['ean']['attribute'] ?? 'data-ean');
        }

        return null;
    }

    protected function extractEanFromJsonLd(DomCrawler $crawler, string $field): ?string
    {
        try {
            $scripts = $crawler->filter('script[type="application/ld+json"]');
            foreach ($scripts as $script) {
                $content = trim($script->textContent);
                if (empty($content)) {
                    continue;
                }

                $data = json_decode($content, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    continue;
                }

                $result = $this->findFieldRecursive($data, $field);
                if ($result) {
                    return (string)$result;
                }
            }
        } catch (\Exception $e) {
            $this->logger->warning('JSON-LD extraction failed: ' . $e->getMessage());
        }

        return null;
    }

    private function findFieldRecursive(array $data, string $field): ?string
    {
        // Direct match
        if (isset($data[$field]) && (is_string($data[$field]) || is_numeric($data[$field]))) {
            return (string)$data[$field];
        }

        // Handle @graph
        if (isset($data['@graph']) && is_array($data['@graph'])) {
            foreach ($data['@graph'] as $item) {
                if (is_array($item)) {
                    $result = $this->findFieldRecursive($item, $field);
                    if ($result) return $result;
                }
            }
        }

        // Handle array of items (e.g. valid JSON-LD can be specific list of objects)
        if (array_keys($data) === range(0, count($data) - 1)) {
            foreach ($data as $item) {
                if (is_array($item)) {
                    $result = $this->findFieldRecursive($item, $field);
                    if ($result) return $result;
                }
            }
        }

        return null;
    }

    protected function extractEanFromMeta(DomCrawler $crawler): ?string
    {
        try {
            $meta = $crawler->filter('meta[itemprop="gtin13"]')->first();
            if ($meta->count()) return $meta->attr('content');
        } catch (\Exception $e) {}

        try {
            $meta = $crawler->filter('meta[property="product:ean"]')->first();
            if ($meta->count()) return $meta->attr('content');
        } catch (\Exception $e) {}

        return null;
    }

    protected function extractEanFromDataAttribute(DomCrawler $crawler, string $attribute): ?string
    {
        try {
            $element = $crawler->filter("[{$attribute}]")->first();
            if ($element->count()) return $element->attr($attribute);
        } catch (\Exception $e) {}

        return null;
    }

    protected function extractTitle(DomCrawler $crawler, array $config): string
    {
        return $crawler->filter($config['selectors']['title'] ?? 'h1')->first()->text();
    }

    protected function extractPrice(DomCrawler $crawler, array $config): float
    {
        $priceText = $crawler->filter($config['selectors']['price'] ?? '.price')->first()->text();
        return $this->priceParser->parse($priceText);
    }
}
