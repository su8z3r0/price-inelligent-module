<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Parser;

use Cyper\PriceIntelligent\Api\ParserInterface;
use Cyper\PriceIntelligent\Api\PriceParserInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;

class FtpParser implements ParserInterface
{
    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly LocalParser $localParser,
        private readonly Csv $csvProcessor,
        private readonly PriceParserInterface $priceParser
    ) {
    }

    public function parse(array $config): array
    {
        // Normalize config keys to support both prefixed and simple keys
        $host = $config['ftp_host'] ?? $config['host'] ?? null;
        $user = $config['ftp_username'] ?? $config['username'] ?? null;
        $pass = $config['ftp_password'] ?? $config['password'] ?? null;
        $path = $config['ftp_path'] ?? $config['path'] ?? null;
        $port = $config['ftp_port'] ?? $config['port'] ?? 21;

        if (!$host || !$user || !$pass || !$path) {
            throw new LocalizedException(__('Parametri FTP mancanti (host, username, password, path)'));
        }

        // Connessione FTP
        $ftpConnection = ftp_connect($host, (int)$port);
        
        if (!$ftpConnection) {
            throw new LocalizedException(__('Impossibile connettersi al server FTP: %1', $host));
        }

        $login = ftp_login($ftpConnection, $user, $pass);
        
        if (!$login) {
            ftp_close($ftpConnection);
            throw new LocalizedException(__('Autenticazione FTP fallita'));
        }

        ftp_pasv($ftpConnection, true);

        // Download file
        $tempDir = $this->directoryList->getPath('var') . '/tmp';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }
        
        $localFile = $tempDir . '/ftp_' . basename($path);
        
        if (!ftp_get($ftpConnection, $localFile, $path, FTP_BINARY)) {
            ftp_close($ftpConnection);
            throw new LocalizedException(__('Impossibile scaricare file FTP: %1', $path));
        }

        ftp_close($ftpConnection);

        // Parse CSV usando lo stesso metodo del LocalParser
        $products = $this->parseCSVFile($localFile, $config);
        
        // Clean up
        @unlink($localFile);
        
        return $products;
    }

    public function getType(): string
    {
        return 'ftp';
    }

    /**
     * Parse CSV file (duplicato da LocalParser per consistenza)
     */
    /**
     * Parse CSV file (duplicato da LocalParser per consistenza)
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
