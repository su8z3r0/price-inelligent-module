<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model;

use Cyper\PriceIntelligent\Api\ParserInterface;
use Cyper\PriceIntelligent\Model\Parser\LocalParser;
use Cyper\PriceIntelligent\Model\Parser\FtpParser;
use Cyper\PriceIntelligent\Model\Parser\HttpParser;
use Magento\Framework\Exception\LocalizedException;

class ParserFactory
{
    public function __construct(
        private readonly LocalParser $localParser,
        private readonly FtpParser $ftpParser,
        private readonly HttpParser $httpParser
    ) {
    }

    /**
     * Crea il parser appropriato in base al tipo
     *
     * @param string $sourceType
     * @return ParserInterface
     * @throws LocalizedException
     */
    public function create(string $sourceType): ParserInterface
    {
        return match($sourceType) {
            'local' => $this->localParser,
            'ftp' => $this->ftpParser,
            'http' => $this->httpParser,
            default => throw new LocalizedException(__('Tipo sorgente non supportato: %1', $sourceType))
        };
    }
}
