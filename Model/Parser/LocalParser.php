<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Parser;

use Cyper\PriceIntelligent\Api\ParserInterface;
use Cyper\PriceIntelligent\Api\SkuNormalizerInterface;

class LocalParser implements ParserInterface
{
    protected $skuNormalizer;

    public function __construct(SkuNormalizerInterface $skuNormalizer)
    {
        $this->skuNormalizer = $skuNormalizer;
    }

    public function parse(array $config): array
    {
        $filePath = $config['file_path'] ?? null;
        if (!$filePath || !file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        $products = [];
        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle);

        $columnMap = $config['columns'] ?? [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);
            $products[] = $this->parseRow($data, $columnMap);
        }

        fclose($handle);
        return $products;
    }

    protected function parseRow(array $row, array $columnMap): array
    {
        $sku = $row[$columnMap['sku']] ?? '';
        $title = $row[$columnMap['title']] ?? '';
        $price = $row[$columnMap['price']] ?? 0;

        // EAN detection (13 digits)
        $ean = (strlen($sku) === 13 && is_numeric($sku)) ? $sku : null;

        return [
            'sku' => $sku,
            'ean' => $ean,
            'normalized_sku' => $this->skuNormalizer->normalize($sku),
            'title' => trim($title),
            'price' => (float) str_replace(',', '.', $price),
        ];
    }

    public function getType(): string
    {
        return 'local';
    }
}
