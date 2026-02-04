<?php

namespace Cyper\PriceIntelligent\Model\ResourceModel\SupplierProducts;

use Cyper\PriceIntelligent\Model\SupplierProducts;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(SupplierProducts::class, \Cyper\PriceIntelligent\Model\ResourceModel\SupplierProducts::class);
    }
}
