<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Controller\Adminhtml\BestSupplierProducts;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cyper_PriceIntelligent::best_supplier_products');
        $resultPage->getConfig()->getTitle()->prepend(__('Best Supplier Products'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cyper_PriceIntelligent::best_supplier_products');
    }
}
