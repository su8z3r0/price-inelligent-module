<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Block\Adminhtml\Competitor\Edit;

use Magento\Backend\Block\Widget\Context;

abstract class GenericButton
{
    public function __construct(
        protected Context $context
    ) {
    }

    public function getCompetitorId()
    {
        return $this->context->getRequest()->getParam('competitor_id');
    }

    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
