<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model\ResourceModel;

class Competitor extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    public function _construct()
    {
        $this->_init('cyper_competitors', 'competitor_id');
    }

}
