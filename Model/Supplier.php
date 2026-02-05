<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model;

use Magento\Framework\Model\AbstractModel;

class Supplier extends AbstractModel
{
    protected $_eventPrefix = 'cyper_supplier';
    protected $_eventObject = 'supplier';
    
    protected function _construct()
    {
        $this->_init(\Cyper\PriceIntelligent\Model\ResourceModel\Supplier::class);
    }
}
