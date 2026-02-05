<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Parser;

use Cyper\PriceIntelligent\Api\ParserInterface;
use Cyper\PriceIntelligent\Api\PriceParserInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Magento\Framework\HTTP\Client\Curl;

class HttpParser implements ParserInterface
{
    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly Curl $curl,
        private readonly Csv $csvProcessor,
        private readonly PriceParserInterface $priceParser
    ) {
    }

    public function parse(array $config): array
    {
        if (!isset($config['http_url'])) {
            throw new LocalizedException(__('http_url non specificato nella configurazione'));
        }

        // Download CSV
        $this->curl->setTimeout(30);
        $this->curl->get($config['http_url']);
        
        if ($this->curl->getStatus() !== 200) {
            throw new LocalizedException(__('Impossibile scaricare CSV da URL: %1 (HTTP %2)', $config['http_url'], $this->curl->getStatus()));
        }

        $csvContent = $this->curl->getBody();
        
        // Salva in temp directory
        $tempDir = $this->directoryList->getPath('var') . '/tmp';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }
        
        $tempFile = $tempDir . '/http_' . md5($config['http_url']) . '.csv';
        file_put_contents($tempFile, $csvContent);

        // Parse CSV
        $products = $this->parseCSVFile($tempFile, $config['columns'] ?? []);
        
        // Clean up
        @unlink($tempFile);
        
        return $products;
    }

    public function getType(): string
    {
        return 'http';
    }

    /**
     * Parse CSV file
     */
    private function parseCSVFile(string $filePath, array $columnMapping): array
    {
        $csvData = $this->csvProcessor->getData($filePath);
        
        if (empty($csvData)) {
            return [];
        }

        $headers = array_shift($csvData);
        
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
