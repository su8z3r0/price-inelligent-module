<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Controller\Adminhtml\PriceComparisons;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    const ADMIN_RESOURCE = 'Cyper_PriceIntelligent::price_comparisons';

    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cyper_PriceIntelligent::price_comparisons');
        $resultPage->getConfig()->getTitle()->prepend(__('Competitiveness Analysis'));
        
        return $resultPage;
    }
}
