<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Controller\Adminhtml\CompetitorPrices;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    const ADMIN_RESOURCE = 'Cyper_PriceIntelligent::competitor_prices';

    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cyper_PriceIntelligent::competitor_prices');
        $resultPage->getConfig()->getTitle()->prepend(__('Competitor Prices'));
        
        return $resultPage;
    }
}
