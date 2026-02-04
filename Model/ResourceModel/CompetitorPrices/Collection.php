<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\ResourceModel\CompetitorPrices;

use Cyper\PriceIntelligent\Model\CompetitorPrices;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(CompetitorPrices::class, \Cyper\PriceIntelligent\Model\ResourceModel\CompetitorPrices::class);
    }
}
