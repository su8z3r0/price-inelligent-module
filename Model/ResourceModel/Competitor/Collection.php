<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\ResourceModel\Competitor;

use Cyper\PriceIntelligent\Model\Competitor;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(Competitor::class, \Cyper\PriceIntelligent\Model\ResourceModel\Competitor::class);
    }
}
