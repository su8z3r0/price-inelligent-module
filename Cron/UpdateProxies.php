<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Cron;

use Cyper\PriceIntelligent\Api\ProxyProviderInterface;
use Psr\Log\LoggerInterface;

class UpdateProxies
{
    public function __construct(
        private readonly ProxyProviderInterface $proxyProvider,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Execute cron job
     *
     * @return void
     */
    public function execute(): void
    {
        $this->logger->info('Starting proxy update cron job...');
        try {
            $this->proxyProvider->updateProxies();
            $this->logger->info('Proxy update cron job completed successfully.');
        } catch (\Exception $e) {
            $this->logger->error('Proxy update cron job failed: ' . $e->getMessage());
        }
    }
}
