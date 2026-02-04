<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Parser;

use Cyper\PriceIntelligent\Api\ParserInterface;

class CsvParser implements ParserInterface
{
    public function parse(array $config): array
    {
        // Implementation here
        return [];
    }

    public function getType(): string
    {
        return 'csv';
    }
}
