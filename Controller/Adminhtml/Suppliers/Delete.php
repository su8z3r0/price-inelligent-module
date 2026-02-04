<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Controller\Adminhtml\Suppliers;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Cyper\PriceIntelligent\Model\SupplierFactory;

class Delete extends Action
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
        $id = $this->getRequest()->getParam('supplier_id');

        if ($id) {
            try {
                $model = $this->supplierFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('The supplier has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['supplier_id' => $id]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a supplier to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
