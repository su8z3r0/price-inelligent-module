<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\ResourceModel\Supplier;

use Cyper\PriceIntelligent\Model\Supplier;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(Supplier::class, \Cyper\PriceIntelligent\Model\ResourceModel\Supplier::class);
    }
}
