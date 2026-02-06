<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Parser;

use Cyper\PriceIntelligent\Api\ParserInterface;
use Cyper\PriceIntelligent\Api\PriceParserInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;

class LocalParser implements ParserInterface
{
    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly Csv $csvProcessor,
        private readonly PriceParserInterface $priceParser
    ) {
    }

    public function parse(array $config): array
    {
        if (!isset($config['file_path'])) {
            throw new LocalizedException(__('file_path non specificato nella configurazione'));
        }

        // Supporta path assoluto o relativo a var/suppliers
        $filePath = $config['file_path'];
        if (!str_starts_with($filePath, '/')) {
            $filePath = $this->directoryList->getPath('var') . '/suppliers/' . $filePath;
        }
        
        if (!file_exists($filePath)) {
            throw new LocalizedException(__('File CSV non trovato: %1', $filePath));
        }

        return $this->parseCSVFile($filePath, $config);
    }

    public function getType(): string
    {
        return 'local';
    }

    /**
     * Parse CSV file with explicit column mapping or auto-normalization
     */
    /**
     * Parse CSV file with explicit column mapping or auto-normalization
     */
    private function parseCSVFile(string $filePath, array $config): array
    {
        if (isset($config['delimiter'])) {
            $this->csvProcessor->setDelimiter($config['delimiter']);
        }
        if (isset($config['enclosure'])) {
            $this->csvProcessor->setEnclosure($config['enclosure']);
        }

        $csvData = $this->csvProcessor->getData($filePath);
        
        // Reset defaults
        $this->csvProcessor->setDelimiter(',');
        $this->csvProcessor->setEnclosure('"');
        
        if (empty($csvData)) {
            return [];
        }

        $headers = array_shift($csvData);
        $columnMapping = $config['columns'] ?? [];
        
        // Build index map from headers
        if (!empty($columnMapping)) {
            $headerIndexMap = $this->buildExplicitMapping($headers, $columnMapping);
        } else {
            $headerIndexMap = $this->buildAutoMapping($headers);
        }

        $products = [];
        foreach ($csvData as $row) {
            $product = $this->mapRow($headerIndexMap, $row);
            if ($product) {
                $products[] = $product;
            }
        }

        return $products;
    }

    /**
     * Build mapping from explicit config
     */
    private function buildExplicitMapping(array $headers, array $columnMapping): array
    {
        $map = [];
        
        foreach ($headers as $index => $header) {
            $normalizedHeader = strtolower(trim($header));
            
            foreach ($columnMapping as $field => $csvColumn) {
                if (strtolower(trim($csvColumn)) === $normalizedHeader) {
                    $map[$field] = $index;
                    break;
                }
            }
        }
        
        return $map;
    }

    /**
     * Build mapping with auto-normalization
     */
    private function buildAutoMapping(array $headers): array
    {
        $map = [];
        
        foreach ($headers as $index => $header) {
            $normalized = $this->normalizeHeader(strtolower(trim($header)));
            if ($normalized) {
                $map[$normalized] = $index;
            }
        }
        
        return $map;
    }

    /**
     * Auto-normalize header
     */
    private function normalizeHeader(string $header): ?string
    {
        if (in_array($header, ['sku', 'codice', 'cod'])) {
            return 'sku';
        }
        
        if (in_array($header, ['titolo_prodotto', 'titolo', 'title'])) {
            return 'title';
        }
        
        if (in_array($header, ['prezzo', 'price', 'prezzo_vendita'])) {
            return 'price';
        }
        
        if (in_array($header, ['ean', 'ean13', 'barcode'])) {
            return 'ean';
        }
        
        return null;
    }

    /**
     * Map CSV row to product
     */
    private function mapRow(array $headerIndexMap, array $row): ?array
    {
        if (!isset($headerIndexMap['sku']) || !isset($headerIndexMap['title']) || !isset($headerIndexMap['price'])) {
            return null;
        }
        
        $product = [];
        $product['sku'] = $row[$headerIndexMap['sku']] ?? null;
        $product['title'] = $row[$headerIndexMap['title']] ?? null;
        $priceText = $row[$headerIndexMap['price']] ?? null;
        
        if (!$product['sku'] || !$product['title'] || !$priceText) {
            return null;
        }
        
        $product['price'] = $this->priceParser->parse($priceText);
        
        if (isset($headerIndexMap['ean'])) {
            $product['ean'] = $row[$headerIndexMap['ean']] ?? null;
        }
        
        return $product;
    }
}
