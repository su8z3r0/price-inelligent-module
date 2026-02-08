<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Console\Command;

use Cyper\PriceIntelligent\Model\BestCompetitorPricesFactory;
use Cyper\PriceIntelligent\Model\ResourceModel\CompetitorPrices\CollectionFactory as CompetitorPricesCollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
class CompetitorFindBestCommand extends Command
{
    public function __construct(
        private readonly CompetitorPricesCollectionFactory $competitorPricesCollectionFactory,
        private readonly BestCompetitorPricesFactory $bestCompetitorPricesFactory,
        private readonly State $state,
        private readonly LoggerInterface $logger,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('cyper:competitor:find-best');
        $this->setDescription('Trova il competitor con il prezzo più basso per ogni SKU/EAN');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (\Exception $e) {
            // Area già impostata
        }

        $output->writeln('<info>Inizio analisi miglior prezzo competitor...</info>');

        try {
            $collection = $this->competitorPricesCollectionFactory->create();
            
            // Raggruppa per SKU
            $collection->getSelect()
                ->reset(\Magento\Framework\DB\Select::COLUMNS)
                ->columns([
                    'sku',
                    'product_title',
                    'sale_price' => new \Zend_Db_Expr('MIN(sale_price)'),
                    'winner_competitor_id' => new \Zend_Db_Expr('(
                        SELECT competitor_id 
                        FROM ' . $collection->getMainTable() . ' AS sub
                        WHERE sub.sku = main_table.sku
                        ORDER BY sub.sale_price ASC 
                        LIMIT 1
                    )')
                ])
                ->group('sku');

            $processed = 0;
            
            foreach ($collection as $item) {
                try {
                    // Cancella record esistente per questo SKU
                    // Note: Factory::create() returns a model instance. getCollection() is on the model resource, not the factory directly usually.
                    // Correct pattern: $factory->create()->getCollection()
                    $existingCollection = $this->bestCompetitorPricesFactory->create()->getCollection();
                    $existingCollection->addFieldToFilter('sku', $item->getSku());
                    
                    foreach ($existingCollection as $existing) {
                        $existing->delete();
                    }

                    // Crea nuovo record
                    $bestPrice = $this->bestCompetitorPricesFactory->create();
                    $bestPrice->setData([
                        'sku' => $item->getSku(),
                        'normalized_sku' => $this->normalizeSku($item->getSku()),
                        'product_title' => $item->getProductTitle(),
                        'sale_price' => $item->getSalePrice(),
                        'winner_competitor_id' => $item->getWinnerCompetitorId(),
                        'winner_competitor_name' => $this->getCompetitorName((int)$item->getWinnerCompetitorId())
                    ]);
                    $bestPrice->save();
                    
                    $processed++;
                } catch (\Exception $e) {
                    $this->logger->error('Error processing best price', [
                        'sku' => $item->getSku(),
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            $output->writeln("<info>✓ Processati {$processed} prodotti</info>");
            $output->writeln('<info>Tabella best_competitor_prices aggiornata con successo!</info>');
            
            return Cli::RETURN_SUCCESS;
            
        } catch (\Exception $e) {
            $output->writeln("<error>Errore durante l'analisi: {$e->getMessage()}</error>");
            $this->logger->error('Find best competitor failed', ['error' => $e->getMessage()]);
            return Cli::RETURN_FAILURE;
        }
    }

    private function normalizeSku(string $sku): string
    {
        return strtoupper(preg_replace('/[^A-Z0-9]/', '', strtoupper($sku)));
    }

    private function getCompetitorName(int $competitorId): string
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $competitor = $objectManager->create(\Cyper\PriceIntelligent\Model\Competitor::class)->load($competitorId);
        return $competitor->getName() ?? 'Unknown';
    }
}
