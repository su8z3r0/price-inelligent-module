<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\ResourceModel\BestSupplierProducts;

use Cyper\PriceIntelligent\Model\BestSupplierProducts;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(BestSupplierProducts::class, \Cyper\PriceIntelligent\Model\ResourceModel\BestSupplierProducts::class);
    }
}
