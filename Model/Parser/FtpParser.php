<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Parser;

use Cyper\PriceIntelligent\Api\ParserInterface;
use Cyper\PriceIntelligent\Model\Supplier;
use Magento\Framework\Exception\LocalizedException;

class FtpParser implements ParserInterface
{
    private const TEMP_FILE = '/tmp/supplier_ftp_temp.csv';

    public function __construct(
        private readonly LocalParser $localParser
    ) {
    }

    public function parse(Supplier $supplier): \Illuminate\Support\Collection
    {
        $config = $supplier->getSourceConfig();
        
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
        $downloaded = ftp_get($ftpConnection, self::TEMP_FILE, $config['remote_path'], FTP_BINARY);
        
        ftp_close($ftpConnection);

        if (!$downloaded) {
            throw new LocalizedException(__('Impossibile scaricare file da FTP'));
        }

        // Crea supplier temporaneo con path locale
        $tempSupplier = clone $supplier;
        $tempSupplier->setSourceConfig(['file_path' => basename(self::TEMP_FILE)]);

        // Usa LocalParser
        $products = $this->localParser->parse($tempSupplier);

        // Pulisci file temporaneo
        if (file_exists(self::TEMP_FILE)) {
            unlink(self::TEMP_FILE);
        }

        return $products;
    }
}
