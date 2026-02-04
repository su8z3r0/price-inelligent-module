<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Api;

interface ParserInterface
{
    /**
     * Parse source and return products data
     *
     * @param array $config
     * @return array
     */
    public function parse(array $config): array;

    /**
     * Get parser type identifier
     *
     * @return string
     */
    public function getType(): string;
}
