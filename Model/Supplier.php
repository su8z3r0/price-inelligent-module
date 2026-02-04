<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model;

class Supplier extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Cyper\PriceIntelligent\Model\ResourceModel\Supplier::class);
    }
}
