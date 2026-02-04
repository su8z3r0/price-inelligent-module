<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Controller\Adminhtml\Suppliers;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Cyper\PriceIntelligent\Model\SupplierFactory;

class Edit extends Action
{
    const ADMIN_RESOURCE = 'Cyper_PriceIntelligent::suppliers';

    protected $resultPageFactory;
    protected $coreRegistry;
    protected $supplierFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        SupplierFactory $supplierFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->supplierFactory = $supplierFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('supplier_id');
        $model = $this->supplierFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This supplier no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->coreRegistry->register('current_supplier', $model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cyper_PriceIntelligent::suppliers');
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ? __('Edit Supplier') : __('New Supplier')
        );

        return $resultPage;
    }
}
