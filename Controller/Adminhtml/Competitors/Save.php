<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Controller\Adminhtml\Competitors;

use Cyper\PriceIntelligent\Api\CompetitorRepositoryInterface;
use Cyper\PriceIntelligent\Model\CompetitorFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;

class Save extends Action
{
    const ADMIN_RESOURCE = 'Cyper_PriceIntelligent::competitors';

    public function __construct(
        Context $context,
        private readonly CompetitorFactory $competitorFactory,
        private readonly CompetitorRepositoryInterface $competitorRepository
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        $id = $this->getRequest()->getParam('competitor_id');

        if ($id) {
            try {
                $model = $this->competitorRepository->getById((int)$id);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('Competitor not found.'));
                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $model = $this->competitorFactory->create();
        }

        // Decodifica crawler_config se è una stringa JSON
        if (isset($data['crawler_config']) && is_string($data['crawler_config'])) {
            $data['crawler_config'] = json_decode($data['crawler_config'], true);
        }

        // Remove competitor_id from data if it's empty (for new competitors)
        // This prevents Magento from trying to UPDATE instead of INSERT
        if (isset($data['competitor_id']) && empty($data['competitor_id'])) {
            unset($data['competitor_id']);
        }

        $model->setData($data);

        try {
            $this->competitorRepository->save($model);
            $this->messageManager->addSuccessMessage(__('Competitor saved successfully.'));
            
            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['competitor_id' => $model->getCompetitorId()]);
            }
            
            return $resultRedirect->setPath('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['competitor_id' => $id]);
        }
    }
}
