<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Parser;

use Cyper\PriceIntelligent\Api\ParserInterface;
use Cyper\PriceIntelligent\Model\Supplier;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DirectoryList;

class FtpParser implements ParserInterface
{
    private const TEMP_FILE = '/tmp/supplier_ftp_temp.csv';

    public function __construct(
        private readonly LocalParser $localParser,
        private readonly DirectoryList $directoryList
    ) {
    }

    public function parse(array $config): array
    {
        if (!isset($config['host'], $config['username'], $config['password'], $config['remote_path'])) {
            throw new LocalizedException(__('Configurazione FTP incompleta'));
        }

        // Connessione FTP
        $ftpConnection = ftp_connect($config['host'], $config['port'] ?? 21);
        
        if (!$ftpConnection) {
            throw new LocalizedException(__('Impossibile connettersi al server FTP'));
        }

        $login = ftp_login($ftpConnection, $config['username'], $config['password']);
        
        if (!$login) {
            ftp_close($ftpConnection);
            throw new LocalizedException(__('Autenticazione FTP fallita'));
        }

        // Modalità passiva
        ftp_pasv($ftpConnection, true);

        // Download file
        $tempDir = $this->directoryList->getPath('var') . '/tmp';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        
        $tempFile = $tempDir . '/ftp_csv_' . time() . '.csv';
        $downloaded = ftp_get($ftpConnection, $tempFile, $config['remote_path'], FTP_BINARY);
        
        ftp_close($ftpConnection);

        if (!$downloaded) {
            throw new LocalizedException(__('Impossibile scaricare file da FTP'));
        }

        // Usa LocalParser
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
        return 'ftp';
    }
}
