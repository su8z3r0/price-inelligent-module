<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Controller\Adminhtml\Suppliers;

use Cyper\PriceIntelligent\Api\SupplierRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends Action
{
    const ADMIN_RESOURCE = 'Cyper_PriceIntelligent::suppliers';

    public function __construct(
        Context $context,
        private readonly SupplierRepositoryInterface $supplierRepository
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('supplier_id');

        if ($id) {
            try {
                $this->supplierRepository->deleteById((int)$id);
                $this->messageManager->addSuccessMessage(__('The supplier has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('Supplier not found.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a supplier to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
