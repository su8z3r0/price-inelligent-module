<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model;

use Cyper\PriceIntelligent\Api\ParserInterface;

class ParserFactory
{
    protected $parsers;

    public function __construct(array $parsers = [])
    {
        $this->parsers = $parsers;
    }

    public function create(string $type): ParserInterface
    {
        if (!isset($this->parsers[$type])) {
            throw new \InvalidArgumentException("Parser type '{$type}' not found");
        }

        return $this->parsers[$type];
    }

    public function getAvailableTypes(): array
    {
        return array_keys($this->parsers);
    }
}
