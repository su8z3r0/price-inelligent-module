<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class ProxyPool
{
    private array $proxies = [];
    private array $failedProxies = [];
    private int $currentIndex = 0;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly \Cyper\PriceIntelligent\Api\ProxyProviderInterface $proxyProvider,
        private readonly LoggerInterface $logger
    ) {
        $this->loadProxies();
    }

    /**
     * Load proxies from configuration and provider
     */
    private function loadProxies(): void
    {
        if (!$this->scopeConfig->isSetFlag('price_intelligent/proxy/enabled')) {
            return;
        }

        // Load from provider (GeoNode)
        try {
            $providerProxies = $this->proxyProvider->getProxies();
            foreach ($providerProxies as $proxy) {
                // Ensure protocol is set
                if (!isset($proxy['protocol'])) {
                    $proxy['protocol'] = 'http';
                }
                $this->proxies[] = $proxy;
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to load proxies from provider: ' . $e->getMessage());
        }

        // Load from configuration (Fallback/Manual overrides)
        $proxiesConfig = $this->scopeConfig->getValue('price_intelligent/proxy/proxies');
        
        if ($proxiesConfig) {
             // Parse proxy configuration (format: url|username|password, one per line)
            $lines = explode("\n", trim($proxiesConfig));
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || str_starts_with($line, '#')) {
                    continue;
                }

                $parts = explode('|', $line);
                if (count($parts) >= 1) {
                    $this->proxies[] = [
                        'url' => trim($parts[0]),
                        'username' => isset($parts[1]) ? trim($parts[1]) : null,
                        'password' => isset($parts[2]) ? trim($parts[2]) : null,
                        'protocol' => 'http' // Default for manual config
                    ];
                }
            }
        }

        $this->logger->info('ProxyPool loaded ' . count($this->proxies) . ' proxies');
    }

    /**
     * Get all available proxies (excluding failed ones)
     */
    public function getAvailableProxies(): array
    {
        return array_filter($this->proxies, function($proxy) {
            return !in_array($proxy['url'], $this->failedProxies);
        });
    }

    /**
     * Get next proxy using round-robin strategy
     */
    public function getNextProxy(): ?array
    {
        $available = $this->getAvailableProxies();
        
        if (empty($available)) {
            return null;
        }

        // Reset to indexed array
        $available = array_values($available);
        
        // Round-robin selection
        $proxy = $available[$this->currentIndex % count($available)];
        $this->currentIndex++;

        return $proxy;
    }

    /**
     * Get random proxy
     */
    public function getRandomProxy(): ?array
    {
        $available = $this->getAvailableProxies();
        
        if (empty($available)) {
            return null;
        }

        return $available[array_rand($available)];
    }

    /**
     * Mark proxy as failed
     */
    public function markAsFailed(string $proxyUrl): void
    {
        if (!in_array($proxyUrl, $this->failedProxies)) {
            $this->failedProxies[] = $proxyUrl;
            $this->logger->warning('Proxy marked as failed: ' . $proxyUrl);
            
            // Remove from provider cache
            try {
                $this->proxyProvider->removeProxy($proxyUrl);
            } catch (\Exception $e) {
                // Ignore if provider doesn't support removal or fails
                $this->logger->warning('Could not remove proxy from provider: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reset failed proxies
     */
    public function resetFailed(): void
    {
        $count = count($this->failedProxies);
        $this->failedProxies = [];
        $this->logger->info('Reset ' . $count . ' failed proxies');
    }

    /**
     * Get total proxy count
     */
    public function getTotalCount(): int
    {
        return count($this->proxies);
    }
}
