<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\ResourceModel\BestCompetitorPrices;

use Cyper\PriceIntelligent\Model\BestCompetitorPrices;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Cyper\PriceIntelligent\Model\BestCompetitorPrices::class,
            \Cyper\PriceIntelligent\Model\ResourceModel\BestCompetitorPrices::class
        );
    }
}
