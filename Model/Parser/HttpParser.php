<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Parser;

use Cyper\PriceIntelligent\Api\ParserInterface;
use Cyper\PriceIntelligent\Model\Supplier;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;

class HttpParser implements ParserInterface
{
    private const TEMP_FILE = '/tmp/supplier_http_temp.csv';

    public function __construct(
        private readonly Curl $curl,
        private readonly LocalParser $localParser
    ) {
    }

    public function parse(Supplier $supplier): \Illuminate\Support\Collection
    {
        $config = $supplier->getSourceConfig();
        
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
        file_put_contents(self::TEMP_FILE, $csvContent);

        // Crea un supplier temporaneo con path locale
        $tempSupplier = clone $supplier;
        $tempSupplier->setSourceConfig(['file_path' => basename(self::TEMP_FILE)]);

        // Usa LocalParser per processare il file
        $products = $this->localParser->parse($tempSupplier);

        // Pulisci file temporaneo
        if (file_exists(self::TEMP_FILE)) {
            unlink(self::TEMP_FILE);
        }

        return $products;
    }
}
