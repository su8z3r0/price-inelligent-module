<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Controller\Adminhtml\Competitors;

use Cyper\PriceIntelligent\Model\CompetitorFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Delete extends Action
{
    const ADMIN_RESOURCE = 'Cyper_PriceIntelligent::competitors';

    public function __construct(
        Context $context,
        private readonly CompetitorFactory $competitorFactory
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('competitor_id');
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($id) {
            try {
                $model = $this->competitorFactory->create();
                $model->load($id);
                $model->delete();
                
                $this->messageManager->addSuccessMessage(__('Competitor eliminato.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
