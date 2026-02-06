<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Controller\Adminhtml\Suppliers;

use Cyper\PriceIntelligent\Api\SupplierRepositoryInterface;
use Cyper\PriceIntelligent\Model\SupplierFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;

class Save extends Action
{
    const ADMIN_RESOURCE = 'Cyper_PriceIntelligent::suppliers';

    public function __construct(
        Context $context,
        private readonly SupplierFactory $supplierFactory,
        private readonly SupplierRepositoryInterface $supplierRepository
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = $this->getRequest()->getParam('supplier_id');

            if ($id) {
                try {
                    $model = $this->supplierRepository->getById((int)$id);
                } catch (NoSuchEntityException $e) {
                    $this->messageManager->addErrorMessage(__('This supplier no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            } else {
                $model = $this->supplierFactory->create();
            }

            // Validate JSON
            if (isset($data['source_config'])) {
                json_decode($data['source_config']);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->messageManager->addErrorMessage(__('Invalid JSON in Source Config'));
                    return $resultRedirect->setPath('*/*/edit', ['supplier_id' => $id]);
                }
            }

            $model->setData($data);

            try {
                $this->supplierRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the supplier.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['supplier_id' => $model->getSupplierId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['supplier_id' => $id]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
