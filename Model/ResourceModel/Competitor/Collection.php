<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\ResourceModel\Competitor;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Cyper\PriceIntelligent\Model\Competitor as CompetitorModel;
use Cyper\PriceIntelligent\Model\ResourceModel\Competitor as CompetitorResource;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CompetitorModel::class, CompetitorResource::class);
    }
}
