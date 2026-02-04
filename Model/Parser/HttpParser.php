<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Parser;

use Cyper\PriceIntelligent\Api\ParserInterface;
use Cyper\PriceIntelligent\Api\SkuNormalizerInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Filesystem\DirectoryList;

class HttpParser implements ParserInterface
{
    protected $skuNormalizer;
    protected $curl;
    protected $directoryList;

    public function __construct(
        SkuNormalizerInterface $skuNormalizer,
        Curl $curl,
        DirectoryList $directoryList
    ) {
        $this->skuNormalizer = $skuNormalizer;
        $this->curl = $curl;
        $this->directoryList = $directoryList;
    }

    public function parse(array $config): array
    {
        $url = $config['http_url'] ?? null;
        if (!$url) {
            throw new \InvalidArgumentException('Missing HTTP URL');
        }

        // Download to temp
        $tempFile = $this->directoryList->getPath('tmp') . '/' . uniqid('supplier_') . '.csv';

        $this->curl->setTimeout(30);
        $this->curl->get($url);

        if ($this->curl->getStatus() !== 200) {
            throw new \RuntimeException("HTTP download failed: {$url}");
        }

        file_put_contents($tempFile, $this->curl->getBody());

        // Parse downloaded file
        $products = $this->parseFile($tempFile, $config);
        unlink($tempFile);

        return $products;
    }

    protected function parseFile(string $filePath, array $config): array
    {
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
        return 'http';
    }
}
