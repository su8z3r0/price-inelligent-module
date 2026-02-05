<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model;

use Cyper\PriceIntelligent\Api\ParserInterface;
use Magento\Framework\Exception\LocalizedException;

class ParserFactory
{
    private array $parsers;

    /**
     * @param array $parsers Associative array of ['type' => ParserInstance]
     */
    public function __construct(
        array $parsers = []
    ) {
        $this->parsers = $parsers;
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
        if (!isset($this->parsers[$sourceType])) {
            throw new LocalizedException(__('Tipo sorgente non supportato: %1', $sourceType));
        }

        return $this->parsers[$sourceType];
    }

    /**
     * Get all available parser types
     *
     * @return array
     */
    public function getAvailableTypes(): array
    {
        return array_keys($this->parsers);
    }
}
