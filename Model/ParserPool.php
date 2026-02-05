<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model;

use Cyper\PriceIntelligent\Api\ParserInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Pool of parsers - Magento-style pattern
 */
class ParserPool
{
    /**
     * @var ParserInterface[]
     */
    private array $parsers;

    /**
     * @param ParserInterface[] $parsers
     */
    public function __construct(
        array $parsers = []
    ) {
        $this->parsers = $parsers;
    }

    /**
     * Get parser by type
     *
     * @param string $type
     * @return ParserInterface
     * @throws LocalizedException
     */
    public function getParser(string $type): ParserInterface
    {
        if (!isset($this->parsers[$type])) {
            throw new LocalizedException(__('Parser type "%1" not found', $type));
        }

        return $this->parsers[$type];
    }

    /**
     * Get all available parser types
     *
     * @return string[]
     */
    public function getAvailableTypes(): array
    {
        return array_keys($this->parsers);
    }

    /**
     * Check if parser type exists
     *
     * @param string $type
     * @return bool
     */
    public function hasParser(string $type): bool
    {
        return isset($this->parsers[$type]);
    }
}
