<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Api;

interface CrawlerInterface
{
    /**
     * Scrape product data from URL
     *
     * @param array $config
     * @param string $url
     * @return array
     */
    public function scrapeProduct(array $config, string $url): array;
}
