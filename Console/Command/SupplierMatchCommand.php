<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Console\Command;

use Cyper\PriceIntelligent\Model\BestSupplierProducts;
use Cyper\PriceIntelligent\Model\ResourceModel\Supplier\CollectionFactory as SupplierCollectionFactory;
use Cyper\PriceIntelligent\Model\Service\SupplierImportService;
use Cyper\PriceIntelligent\Model\Supplier;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SupplierMatchCommand extends Command
{
    private const OPTION_SUPPLIER = 'supplier';

    public function __construct(
        private readonly SupplierImportService $supplierImportService,
        private readonly SupplierCollectionFactory $supplierCollectionFactory,
        private readonly BestSupplierProducts $bestSupplierProductsFactory,
        private readonly State $state,
        private readonly LoggerInterface $logger,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('cyper:supplier:match');
        $this->setDescription('Importa prodotti da fornitori CSV e trova il miglior prezzo');
        $this->addOption(
            self::OPTION_SUPPLIER,
            's',
            InputOption::VALUE_OPTIONAL,
            'ID del fornitore specifico da importare'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (\Exception $e) {
            // Area già impostata
        }

        $output->writeln('<info>Inizio import fornitori...</info>');

        $supplierId = $input->getOption(self::OPTION_SUPPLIER);

        if ($supplierId) {
            return $this->importSingleSupplier((int)$supplierId, $output);
        }

        return $this->importAllSuppliers($output);
    }

    private function importSingleSupplier(int $supplierId, OutputInterface $output): int
    {
        $collection = $this->supplierCollectionFactory->create();
        $supplier = $collection->getItemById($supplierId);

        if (!$supplier || !$supplier->getId()) {
            $output->writeln("<error>Fornitore con ID {$supplierId} non trovato</error>");
            return Cli::RETURN_FAILURE;
        }

        $output->writeln("<info>Import: {$supplier->getName()}</info>");

        try {
            $count = $this->supplierImportService->importSupplier($supplier, $output);
            $output->writeln("<info>✓ Importati {$count} prodotti da {$supplier->getName()}</info>");
            
            // Trova best prices
            $this->findBestPrices($output);
            
            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>✗ Errore durante l'import di {$supplier->getName()}: {$e->getMessage()}</error>");
            $this->logger->error('Import failed', [
                'supplier_id' => $supplierId,
                'error' => $e->getMessage()
            ]);
            return Cli::RETURN_FAILURE;
        }
    }

    private function importAllSuppliers(OutputInterface $output): int
    {
        $collection = $this->supplierCollectionFactory->create();
        $collection->addFieldToFilter('is_active', 1);

        if ($collection->getSize() === 0) {
            $output->writeln('<comment>Nessun fornitore attivo trovato</comment>');
            return Cli::RETURN_SUCCESS;
        }

        $totalImported = 0;
        foreach ($collection as $supplier) {
            try {
                $output->writeln("<info>Import: {$supplier->getName()}</info>");
                $count = $this->supplierImportService->importSupplier($supplier, $output);
                $output->writeln("<info>✓ Importati {$count} prodotti</info>");
                $totalImported += $count;
            } catch (\Exception $e) {
                $output->writeln("<error>✗ Errore: {$e->getMessage()}</error>");
                $this->logger->error('Import failed', [
                    'supplier' => $supplier->getName(),
                    'error' => $e->getMessage()
                ]);
            }
        }

        $output->writeln("<info>Totale prodotti importati: {$totalImported}</info>");
        
        // Trova best prices
        $this->findBestPrices($output);
        
        return Cli::RETURN_SUCCESS;
    }

    private function findBestPrices(OutputInterface $output): void
    {
        $output->writeln('<info>Calcolo miglior prezzo fornitore per SKU...</info>');
        
        try {
            $connection = $this->bestSupplierProductsFactory->getResource()->getConnection();
            $supplierProductsTable = $connection->getTableName('cyper_supplier_products');
            $bestTable = $connection->getTableName('cyper_best_supplier_products');
            
            // Cancella vecchi dati
            $connection->truncateTable($bestTable);
            
            // Inserisci nuovi best prices
            $select = $connection->select()
                ->from($supplierProductsTable, [
                    'sku',
                    'ean',
                    'normalized_sku',
                    'title',
                    'price' => new \Zend_Db_Expr('MIN(price)'),
                    'winner_supplier_id' => new \Zend_Db_Expr('(
                        SELECT supplier_id 
                        FROM ' . $supplierProductsTable . ' AS sub
                        WHERE COALESCE(sub.ean, sub.sku) = COALESCE(main_table.ean, main_table.sku)
                        ORDER BY sub.price ASC 
                        LIMIT 1
                    )'),
                    'winner_supplier_name' => new \Zend_Db_Expr('(
                        SELECT s.name 
                        FROM ' . $connection->getTableName('cyper_suppliers') . ' AS s
                        WHERE s.supplier_id = (
                            SELECT supplier_id 
                            FROM ' . $supplierProductsTable . ' AS sub
                            WHERE COALESCE(sub.ean, sub.sku) = COALESCE(main_table.ean, main_table.sku)
                            ORDER BY sub.price ASC 
                            LIMIT 1
                        )
                    )'),
                    'created_at' => new \Zend_Db_Expr('NOW()'),
                    'updated_at' => new \Zend_Db_Expr('NOW()')
                ])
                ->group('COALESCE(ean, sku)');
            
            $connection->query(
                $connection->insertFromSelect($select, $bestTable, [
                    'sku', 'ean', 'normalized_sku', 'title', 'price', 
                    'winner_supplier_id', 'winner_supplier_name', 
                    'created_at', 'updated_at'
                ])
            );
            
            $output->writeln('<info>✓ Tabella best_supplier_products aggiornata!</info>');
            
        } catch (\Exception $e) {
            $output->writeln("<error>Errore calcolo best prices: {$e->getMessage()}</error>");
            $this->logger->error('Find best supplier prices failed', ['error' => $e->getMessage()]);
        }
    }
}
