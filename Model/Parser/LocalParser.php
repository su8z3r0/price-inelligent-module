<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Parser;

use Cyper\PriceIntelligent\Api\ParserInterface;
use Cyper\PriceIntelligent\Model\Supplier;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\File\Csv as CsvProcessor;

class LocalParser implements ParserInterface
{
    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly CsvProcessor $csvProcessor
    ) {
    }

    public function parse(array $config): array
    {
        if (!isset($config['file_path'])) {
            throw new LocalizedException(__('file_path non specificato nella configurazione'));
        }

        $filePath = $this->directoryList->getPath('var') . '/suppliers/' . $config['file_path'];
        
        if (!file_exists($filePath)) {
            throw new LocalizedException(__('File CSV non trovato: %1', $filePath));
        }

        $csvData = $this->csvProcessor->getData($filePath);
        
        if (empty($csvData)) {
            return [];
        }

        // Prima riga = intestazioni
        $headers = array_shift($csvData);
        $headers = $this->normalizeHeaders($headers);

        $products = [];
        foreach ($csvData as $row) {
            $product = $this->mapRow($headers, $row);
            if ($product) {
                $products[] = $product;
            }
        }

        return $products;
    }

    public function getType(): string
    {
        return 'local';
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(function($header) {
            $header = strtolower(trim($header));
            
            // Normalizza SKU
            if (in_array($header, ['sku', 'codice', 'cod'])) {
                return 'sku';
            }
            
            // Normalizza titolo
            if (in_array($header, ['titolo_prodotto', 'titolo', 'title'])) {
                return 'title';
            }
            
            // Normalizza prezzo
            if (in_array($header, ['prezzo', 'price', 'prezzo_vendita'])) {
                return 'price';
            }
            
            // EAN
            if (in_array($header, ['ean', 'ean13', 'barcode'])) {
                return 'ean';
            }
            
            return $header;
        }, $headers);
    }

    private function mapRow(array $headers, array $row): ?array
    {
        $data = array_combine($headers, $row);
        
        if (!isset($data['sku']) || !isset($data['title']) || !isset($data['price'])) {
            return null; // Riga invalida
        }

        return [
            'sku' => trim($data['sku']),
            'title' => trim($data['title']),
            'price' => $this->parsePrice($data['price']),
            'ean' => isset($data['ean']) ? trim($data['ean']) : null
        ];
    }

    private function parsePrice(string $priceText): float
    {
        $clean = preg_replace('/[^0-9,.]/', '', $priceText);
        
        if (empty($clean)) {
            return 0.0;
        }

        // Gestisce formati europei (1.200,50) e americani (1,200.50)
        if (substr_count($clean, ',') === 1 && substr_count($clean, '.') >= 1) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } elseif (substr_count($clean, ',') >= 1) {
            $clean = str_replace(',', '', $clean);
        } elseif (substr_count($clean, ',') === 1) {
            $clean = str_replace(',', '.', $clean);
        }

        return (float) $clean;
    }
}
