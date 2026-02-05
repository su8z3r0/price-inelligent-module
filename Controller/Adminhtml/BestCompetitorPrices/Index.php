<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Controller\Adminhtml\BestCompetitorPrices;

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
        $resultPage->setActiveMenu('Cyper_PriceIntelligent::best_competitor_prices');
        $resultPage->getConfig()->getTitle()->prepend(__('Best Competitor Prices'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cyper_PriceIntelligent::best_competitor_prices');
    }
}
