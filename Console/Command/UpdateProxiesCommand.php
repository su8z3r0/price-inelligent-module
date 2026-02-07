<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Console\Command;

use Cyper\PriceIntelligent\Api\ProxyProviderInterface;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;

class UpdateProxiesCommand extends Command
{
    /**
     * @var ProxyProviderInterface
     */
    private $proxyProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ProxyProviderInterface $proxyProvider,
        LoggerInterface $logger,
        string $name = null
    ) {
        $this->proxyProvider = $proxyProvider;
        $this->logger = $logger;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('cyper:proxy:update');
        $this->setDescription('Update proxies from GeoNode API');
        
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Starting proxy update...</info>');
        
        try {
            $this->proxyProvider->updateProxies();
            $output->writeln('<info>Proxies updated successfully.</info>');
            $this->logger->info('Manual proxy update executed successfully.');
            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error updating proxies: ' . $e->getMessage() . '</error>');
            $this->logger->error('Manual proxy update failed: ' . $e->getMessage());
            return Cli::RETURN_FAILURE;
        }
    }
}
