<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Controller\Adminhtml\Suppliers;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Cyper\PriceIntelligent\Model\SupplierFactory;

class Save extends Action
{
    const ADMIN_RESOURCE = 'Cyper_PriceIntelligent::suppliers';

    protected $supplierFactory;

    public function __construct(
        Context $context,
        SupplierFactory $supplierFactory
    ) {
        parent::__construct($context);
        $this->supplierFactory = $supplierFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = $this->getRequest()->getParam('supplier_id');
            $model = $this->supplierFactory->create();

            if ($id) {
                $model->load($id);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This supplier no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
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
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the supplier.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['supplier_id' => $model->getId()]);
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
