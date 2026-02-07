<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Api;

interface PriceParserInterface
{
    /**
     * Parse price from text string
     *
     * @param string $priceText
     * @return float
     */
    public function parse(string $priceText): float;
}
