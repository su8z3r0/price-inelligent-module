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

            $proxies = [];
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

                $proxies[] = [
                    'url' => $item['ip'] . ':' . $item['port'],
                    'protocol' => $protocol,
                    'username' => null, 
                    'password' => null,
                    'latency' => $latency,
                    'anonymity' => $anonymity
                ];
            }

            if (!empty($proxies)) {
                $this->cache->save(
                    $this->serializer->serialize($proxies),
                    self::CACHE_KEY,
                    ['price_intelligent_proxies'],
                    self::CACHE_LIFETIME
                );
                $this->logger->info('Updated proxy list with ' . count($proxies) . ' Optimized Proxies (Latency < ' . ($maxLatency ?: 'Inf') . 'ms, SOCKS Only)');
            } else {
                $this->logger->warning('No optimized proxies found. Check API or Max Latency settings.');
            }

        } catch (\Exception $e) {
            $this->logger->error('Failed to update proxies from GeoNode: ' . $e->getMessage());
        }
    }
}
