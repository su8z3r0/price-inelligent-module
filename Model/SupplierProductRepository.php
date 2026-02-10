<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model;

use Cyper\PriceIntelligent\Api\SupplierProductRepositoryInterface;
use Cyper\PriceIntelligent\Model\ResourceModel\SupplierProducts as ResourceModel;
use Cyper\PriceIntelligent\Model\ResourceModel\SupplierProducts\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class SupplierProductRepository implements SupplierProductRepositoryInterface
{
    /**
     * @var ResourceModel
     */
    protected $resource;

    /**
     * @var SupplierProductsFactory
     */
    protected $supplierProductsFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @param ResourceModel $resource
     * @param SupplierProductsFactory $supplierProductsFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ResourceModel $resource,
        SupplierProductsFactory $supplierProductsFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->resource = $resource;
        $this->supplierProductsFactory = $supplierProductsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(SupplierProducts $supplierProduct)
    {
        try {
            $this->resource->save($supplierProduct);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the supplier product: %1', $exception->getMessage()),
                $exception
            );
        }
        return $supplierProduct;
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        $supplierProduct = $this->supplierProductsFactory->create();
        $this->resource->load($supplierProduct, $id);
        if (!$supplierProduct->getId()) {
            throw new NoSuchEntityException(__('Supplier product with id "%1" does not exist.', $id));
        }
        return $supplierProduct;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function delete(SupplierProducts $supplierProduct)
    {
        try {
            $this->resource->delete($supplierProduct);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the supplier product: %1', $exception->getMessage()),
                $exception
            );
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        return $this->delete($this->get($id));
    }
}
