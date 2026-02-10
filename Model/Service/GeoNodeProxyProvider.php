<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Service;

use Cyper\PriceIntelligent\Api\ProxyProviderInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class GeoNodeProxyProvider implements ProxyProviderInterface
{
    private const API_URL = 'https://proxylist.geonode.com/api/proxy-list?limit=50&page=1&sort_by=latency&sort_type=asc&protocols=socks5,socks4&anonymityLevel=elite&anonymityLevel=anonymous';
    private const CACHE_KEY = 'cyper_price_intelligent_proxies';
    private const CACHE_LIFETIME = 3600; // 1 hour default

    public function __construct(
        private readonly Curl $curl,
        private readonly CacheInterface $cache,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
        private readonly \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {}

    public function getProxies(): array
    {
        $cachedProxies = $this->cache->load(self::CACHE_KEY);
        if ($cachedProxies) {
            try {
                return $this->serializer->unserialize($cachedProxies);
            } catch (\Exception $e) {
                $this->logger->warning('Failed to unserialize cached proxies: ' . $e->getMessage());
            }
        }

        // If no cache, try to update
        $this->updateProxies();
        
        $cachedProxies = $this->cache->load(self::CACHE_KEY);
        return $cachedProxies ? $this->serializer->unserialize($cachedProxies) : [];
    }

    public function updateProxies(): void
    {
        try {
            // Get max latency config (default: no limit)
            $maxLatency = (int)$this->scopeConfig->getValue('price_intelligent/proxy/max_latency');
            
            // Fetch proxies from GeoNode
            // Query optimized to return Low Latency, SOCKS, Elite/Anonymous proxies first
            $this->curl->get(self::API_URL);
            $response = $this->curl->getBody();
            
            if (!$response) {
                throw new \RuntimeException('Empty response from GeoNode API');
            }

            $data = $this->serializer->unserialize($response);
            
            if (!isset($data['data']) || !is_array($data['data'])) {
                throw new \RuntimeException('Invalid response format from GeoNode API');
            }

            $candidates = [];
            foreach ($data['data'] as $item) {
                if (empty($item['ip']) || empty($item['port']) || empty($item['protocols'])) {
                    continue;
                }

                // SECURITY CHECK: Verify Anonymity
                // Even though API filters it, double check to prevent leaks
                $anonymity = strtolower($item['anonymityLevel'] ?? '');
                if ($anonymity === 'transparent') {
                    continue; 
                }

                // Filter by Latency (User Config)
                $latency = $item['latency'] ?? $item['speed'] ?? 9999;
                if ($maxLatency > 0 && $latency > $maxLatency) {
                    continue;
                }
                
                // Filter by Uptime (Minimum 50% required to be considered reliable)
                $uptime = $item['upTime'] ?? 0;
                if ($uptime < 50) {
                     continue;
                }

                // Prefer protocols in order: socks5, socks4, http
                $protocol = 'http';
                if (in_array('socks5', $item['protocols'])) {
                    $protocol = 'socks5';
                } elseif (in_array('socks4', $item['protocols'])) {
                    $protocol = 'socks4';
                }

                $candidates[] = [
                    'url' => $item['ip'] . ':' . $item['port'],
                    'protocol' => $protocol,
                    'username' => null, 
                    'password' => null,
                    'latency' => $latency,
                    'anonymity' => $anonymity
                ];
            }

            // --- PROACTIVE VALIDATION STEP ---
            $validProxies = $this->validateProxies($candidates);

            if (!empty($validProxies)) {
                $this->cache->save(
                    $this->serializer->serialize($validProxies),
                    self::CACHE_KEY,
                    ['price_intelligent_proxies'],
                    self::CACHE_LIFETIME
                );
                $this->logger->info('Updated proxy list with ' . count($validProxies) . ' Validated Proxies (from ' . count($candidates) . ' candidates)');
            } else {
                $this->logger->warning('No working proxies found after validation.');
            }

        } catch (\Exception $e) {
            $this->logger->error('Failed to update proxies from GeoNode: ' . $e->getMessage());
        }
    }

    /**
     * Validate proxies in parallel using curl_multi
     * 
     * @param array $proxies
     * @return array
     */
    private function validateProxies(array $proxies): array
    {
        if (empty($proxies)) {
            return [];
        }

        $this->logger->info('Validating ' . count($proxies) . ' proxies...');
        
        $mh = curl_multi_init();
        $channels = [];
        $validProxies = [];
        
        // Target URL for validation (lightweight and reliable)
        $testUrl = 'http://www.google.com';

        foreach ($proxies as $key => $proxy) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $testUrl);
            curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request only
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Short timeout for validation
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            
            $proxyUrl = $proxy['url'];
            curl_setopt($ch, CURLOPT_PROXY, $proxyUrl);
            
            if ($proxy['protocol'] === 'socks5') {
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            } elseif ($proxy['protocol'] === 'socks4') {
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
            }
            
            curl_multi_add_handle($mh, $ch);
            $channels[$key] = $ch;
        }

        // Execute handles
        $active = null;
        do {
            $status = curl_multi_exec($mh, $active);
            if ($active) {
                // Wait a short time for more activity
                curl_multi_select($mh);
            }
        } while ($active && $status == CURLM_OK);

        // Collect results
        foreach ($channels as $key => $ch) {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($httpCode >= 200 && $httpCode < 400) {
                $validProxies[] = $proxies[$key];
            } else {
                // Determine error for logging (optional, verbose)
                // $error = curl_error($ch);
            }
            
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        
        curl_multi_close($mh);
        
        return $validProxies;
    }

    public function removeProxy(string $proxyUrl): void
    {
        try {
            $cachedProxies = $this->cache->load(self::CACHE_KEY);
            if (!$cachedProxies) {
                return;
            }

            $proxies = $this->serializer->unserialize($cachedProxies);
            if (!is_array($proxies)) {
                return;
            }

            $originalCount = count($proxies);
            $proxies = array_filter($proxies, function ($proxy) use ($proxyUrl) {
                return $proxy['url'] !== $proxyUrl;
            });

            if (count($proxies) < $originalCount) {
                // Re-index array
                $proxies = array_values($proxies);
                
                $this->cache->save(
                    $this->serializer->serialize($proxies),
                    self::CACHE_KEY,
                    ['price_intelligent_proxies'],
                    self::CACHE_LIFETIME
                );
                $this->logger->info('Removed failed proxy from cache: ' . $proxyUrl);
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove proxy from cache: ' . $e->getMessage());
        }
    }
}
