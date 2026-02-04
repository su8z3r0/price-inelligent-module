<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Cyper\PriceIntelligent\Model\ParserFactory;

class SourceType implements OptionSourceInterface
{
    protected $parserFactory;
    protected $labels;

    public function __construct(
        ParserFactory $parserFactory,
        array $labels = []
    ) {
        $this->parserFactory = $parserFactory;
        $this->labels = $labels;
    }

    public function toOptionArray()
    {
        $options = [];
        foreach ($this->parserFactory->getAvailableTypes() as $type) {
            $options[] = [
                'value' => $type,
                'label' => __($this->labels[$type] ?? ucfirst($type))
            ];
        }
        return $options;
    }
}
