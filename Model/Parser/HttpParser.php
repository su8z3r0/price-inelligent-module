<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Parser;

use Cyper\PriceIntelligent\Api\ParserInterface;
use Cyper\PriceIntelligent\Model\Supplier;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Filesystem\DirectoryList;

class HttpParser implements ParserInterface
{
    private const TEMP_FILE = '/tmp/supplier_http_temp.csv';

    public function __construct(
        private readonly Curl $curl,
        private readonly LocalParser $localParser,
        private readonly DirectoryList $directoryList
    ) {
    }

    public function parse(array $config): array
    {
        if (!isset($config['url'])) {
            throw new LocalizedException(__('URL non specificato nella configurazione'));
        }

        // Download CSV
        $this->curl->setTimeout(30);
        $this->curl->get($config['url']);
        
        if ($this->curl->getStatus() !== 200) {
            throw new LocalizedException(__('Impossibile scaricare CSV da URL'));
        }

        $csvContent = $this->curl->getBody();
        
        // Salva in temp directory
        $tempDir = $this->directoryList->getPath('var') . '/tmp';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        
        $tempFile = $tempDir . '/http_csv_' . time() . '.csv';
        file_put_contents($tempFile, $csvContent);

        // Usa LocalParser per processare il file
        $localConfig = ['file_path' => basename($tempFile)];
        $products = $this->localParser->parse($localConfig);

        // Pulisci file temporaneo
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }

        return $products;
    }

    public function getType(): string
    {
        return 'http';
    }
}
