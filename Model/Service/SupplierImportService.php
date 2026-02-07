<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Service;

use Cyper\PriceIntelligent\Model\SupplierProducts;
use Cyper\PriceIntelligent\Model\Supplier;
use Cyper\PriceIntelligent\Model\ParserFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SupplierImportService
{
    public function __construct(
        private readonly ParserFactory $parserFactory,
        private readonly SupplierProducts $supplierProductsFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Importa prodotti da un fornitore
     *
     * @param Supplier $supplier
     * @param OutputInterface|null $output
     * @return int Numero di prodotti importati
     * @throws LocalizedException
     */
    public function importSupplier(Supplier $supplier, ?OutputInterface $output = null): int
    {
        if (!$supplier->getIsActive()) {
            throw new LocalizedException(__('Il fornitore non è attivo'));
        }

        $parser = $this->parserFactory->create($supplier->getSourceType());
        $products = $parser->parse($supplier->getSourceConfig());

        if ($output) {
            $output->writeln("<comment>Trovati " . count($products) . " prodotti nel CSV</comment>");
        }

        $imported = 0;
        foreach ($products as $productData) {
            // Se SKU manca ma EAN c'è, usa EAN come SKU
            if (empty($productData['sku']) && !empty($productData['ean'])) {
                $productData['sku'] = $productData['ean'];
            }
            
            // Trim whitespace
            $productData['sku'] = trim((string)$productData['sku']);
            if (isset($productData['ean'])) {
                $productData['ean'] = trim((string)$productData['ean']);
            }

            // Skip se SKU è ancora vuoto dopo il fallback
            if (empty($productData['sku'])) {
                continue;
            }

            try {
                $this->saveProduct($supplier, $productData);
                $imported++;
            } catch (\Exception $e) {
                $this->logger->error('Failed to import product', [
                    'supplier' => $supplier->getName(),
                    'product' => $productData,
                    'error' => $e->getMessage()
                ]);
                
                if ($output) {
                    $output->writeln("<error>Errore prodotto SKU {$productData['sku']}: {$e->getMessage()}</error>");
                }
            }
        }

        return $imported;
    }

    /**
     * Salva o aggiorna un prodotto fornitore
     */
    private function saveProduct(Supplier $supplier, array $productData): void
    {
        $supplierProduct = $this->supplierProductsFactory;
        
        $supplierProduct->setData([
            'supplier_id' => $supplier->getId(),
            'sku' => $productData['sku'],
            'ean' => $productData['ean'] ?? null,
            'normalized_sku' => $this->normalizeSku($productData['sku']),
            'title' => $productData['title'],
            'price' => $productData['price'],
            'imported_at' => date('Y-m-d H:i:s')
        ]);
        
        $supplierProduct->save();
    }

    /**
     * Normalizza SKU rimuovendo caratteri speciali
     */
    private function normalizeSku(string $sku): string
    {
        return strtoupper(preg_replace('/[^A-Z0-9]/', '', strtoupper($sku)));
    }
}
