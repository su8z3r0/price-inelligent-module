<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Api;

interface ProxyProviderInterface
{
    /**
     * Get list of proxies
     *
     * @return array
     */
    public function getProxies(): array;

    /**
     * Update/Refresh proxy list from source
     *
     * @return void
     */
    public function updateProxies(): void;

    /**
     * Remove a specific proxy from the list (and cache/storage)
     *
     * @param string $proxyUrl
     * @return void
     */
    public function removeProxy(string $proxyUrl): void;
}
