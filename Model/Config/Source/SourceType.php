<?php

namespace Cyper\PriceIntelligent\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class SourceType implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'csv', 'label' => __('CSV Import')],
            ['value' => 'api', 'label' => __('API Integration')],
            ['value' => 'xml', 'label' => __('XML Feed')],
            ['value' => 'scraping', 'label' => __('Web Scraping')]
        ];
    }
}
