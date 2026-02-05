<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Api;

interface ProxyRotatorInterface
{
    /**
     * Get next available proxy from the pool
     *
     * @return array|null Proxy configuration array or null if no proxy available
     */
    public function getNextProxy(): ?array;

    /**
     * Mark a proxy as failed
     *
     * @param string $proxyUrl
     * @return void
     */
    public function markProxyAsFailed(string $proxyUrl): void;

    /**
     * Reset all failed proxies (useful for retry logic)
     *
     * @return void
     */
    public function resetFailedProxies(): void;

    /**
     * Check if proxy rotation is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool;
}
