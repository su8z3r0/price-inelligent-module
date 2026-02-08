<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model;

use Cyper\PriceIntelligent\Api\ParserInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Factory for creating parsers via Pool
 */
class ParserFactory
{
    public function __construct(
        private readonly ParserPool $parserPool
    ) {
    }

    /**
     * Create parser by type
     *
     * @param string $sourceType
     * @return ParserInterface
     * @throws LocalizedException
     */
    public function create(string $sourceType): ParserInterface
    {
        return $this->parserPool->getParser($sourceType);
    }

    /**
     * Get all available parser types
     *
     * @return string[]
     */
    public function getAvailableTypes(): array
    {
        return $this->parserPool->getAvailableTypes();
    }
}
