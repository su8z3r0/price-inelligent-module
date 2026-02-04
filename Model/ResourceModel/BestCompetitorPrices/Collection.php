<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\ResourceModel\BestCompetitorPrices;

use Cyper\PriceIntelligent\Model\BestCompetitorPrices;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(BestCompetitorPrices::class, \Cyper\PriceIntelligent\Model\ResourceModel\BestCompetitorPrices::class);
    }
}
