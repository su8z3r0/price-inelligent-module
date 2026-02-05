<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model;

use Magento\Framework\Model\AbstractModel;

class Competitor extends AbstractModel
{
    protected $_eventPrefix = 'cyper_competitor';
    protected $_eventObject = 'competitor';
    
    protected function _construct()
    {
        $this->_init(\Cyper\PriceIntelligent\Model\ResourceModel\Competitor::class);
    }
}
