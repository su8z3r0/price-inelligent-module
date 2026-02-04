<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\ResourceModel;

class BestSupplierProducts extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    public function _construct()
    {
        $this->_init('cyper_best_supplier_products', 'id');
    }

}
