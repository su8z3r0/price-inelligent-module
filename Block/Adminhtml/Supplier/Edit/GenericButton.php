<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Block\Adminhtml\Supplier\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

abstract class GenericButton
{
    protected $context;
    protected $registry;

    public function __construct(
        Context $context,
        Registry $registry
    ) {
        $this->context = $context;
        $this->registry = $registry;
    }

    public function getSupplierId()
    {
        $supplier = $this->registry->registry('current_supplier');
        return $supplier ? $supplier->getId() : null;
    }

    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
