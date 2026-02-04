<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Service;

use Cyper\PriceIntelligent\Api\CrawlerInterface;
use Cyper\PriceIntelligent\Api\PriceParserInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class Crawler implements CrawlerInterface
{
    protected $curl;
    protected $logger;
    protected $priceParser;

    public function __construct(
        Curl $curl,
        LoggerInterface $logger,
        PriceParserInterface $priceParser
    ) {
        $this->curl = $curl;
        $this->logger = $logger;
        $this->priceParser = $priceParser;
    }

    public function scrapeProduct(array $config, string $url): array
    {
        try {
            $this->curl->setTimeout(30);
            $this->curl->setOption(CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            $this->curl->get($url);

            $html = $this->curl->getBody();
            $crawler = new DomCrawler($html);

            return [
                'product_url' => $url,
                'ean' => $this->extractEan($crawler, $config),
                'product_title' => trim($this->extractTitle($crawler, $config)),
                'sale_price' => $this->extractPrice($crawler, $config),
                'scraped_at' => date('Y-m-d H:i:s'),
            ];
        } catch (\Exception $e) {
            $this->logger->error('Scraping failed', ['url' => $url, 'error' => $e->getMessage()]);
            throw $e;
        }
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
                $data = json_decode($script->textContent, true);

                if (isset($data[$field])) {
                    return $data[$field];
                }

                if (isset($data['@graph'])) {
                    foreach ($data['@graph'] as $item) {
                        if (($item['@type'] ?? '') === 'Product' && isset($item[$field])) {
                            return $item[$field];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->warning('JSON-LD extraction failed: ' . $e->getMessage());
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
