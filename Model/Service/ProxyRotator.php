<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Service;

use Cyper\PriceIntelligent\Api\ProxyRotatorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class ProxyRotator implements ProxyRotatorInterface
{
    private const CONFIG_PATH_ENABLED = 'price_intelligent/proxy/enabled';
    private const CONFIG_PATH_STRATEGY = 'price_intelligent/proxy/rotation_strategy';

    public function __construct(
        private readonly ProxyPool $proxyPool,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly LoggerInterface $logger
    ) {
    }

    public function getNextProxy(): ?array
    {
        if (!$this->isEnabled()) {
            return null;
        }

        $strategy = $this->scopeConfig->getValue(self::CONFIG_PATH_STRATEGY) ?: 'round_robin';
        
        $proxy = match($strategy) {
            'random' => $this->proxyPool->getRandomProxy(),
            default => $this->proxyPool->getNextProxy(),
        };

        if ($proxy) {
            $this->logger->info('Selected proxy: ' . $proxy['url'] . ' (strategy: ' . $strategy . ')');
        }

        return $proxy;
    }

    public function markProxyAsFailed(string $proxyUrl): void
    {
        $this->proxyPool->markAsFailed($proxyUrl);
    }

    public function resetFailedProxies(): void
    {
        $this->proxyPool->resetFailed();
    }

    public function isEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(self::CONFIG_PATH_ENABLED);
    }
}
