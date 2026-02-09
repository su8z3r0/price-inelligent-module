<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Controller\Adminhtml\Competitors;

use Cyper\PriceIntelligent\Api\CompetitorRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends Action
{
    const ADMIN_RESOURCE = 'Cyper_PriceIntelligent::competitors';

    public function __construct(
        Context $context,
        private readonly CompetitorRepositoryInterface $competitorRepository
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('competitor_id');
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($id) {
            try {
                $this->competitorRepository->deleteById((int)$id);
                $this->messageManager->addSuccessMessage(__('Competitor deleted.'));
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('Competitor not found.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
