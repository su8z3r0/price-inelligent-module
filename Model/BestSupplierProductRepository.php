<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model;

use Cyper\PriceIntelligent\Api\BestSupplierProductRepositoryInterface;
use Cyper\PriceIntelligent\Model\ResourceModel\BestSupplierProducts as ResourceModel;
use Cyper\PriceIntelligent\Model\ResourceModel\BestSupplierProducts\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class BestSupplierProductRepository implements BestSupplierProductRepositoryInterface
{
    /**
     * @var ResourceModel
     */
    protected $resource;

    /**
     * @var BestSupplierProductsFactory
     */
    protected $bestSupplierProductsFactory;

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
     * @param BestSupplierProductsFactory $bestSupplierProductsFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ResourceModel $resource,
        BestSupplierProductsFactory $bestSupplierProductsFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->resource = $resource;
        $this->bestSupplierProductsFactory = $bestSupplierProductsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(BestSupplierProducts $bestSupplierProduct)
    {
        try {
            $this->resource->save($bestSupplierProduct);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the best supplier product: %1', $exception->getMessage()),
                $exception
            );
        }
        return $bestSupplierProduct;
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        $bestSupplierProduct = $this->bestSupplierProductsFactory->create();
        $this->resource->load($bestSupplierProduct, $id);
        if (!$bestSupplierProduct->getId()) {
            throw new NoSuchEntityException(__('Best supplier product with id "%1" does not exist.', $id));
        }
        return $bestSupplierProduct;
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
    public function delete(BestSupplierProducts $bestSupplierProduct)
    {
        try {
            $this->resource->delete($bestSupplierProduct);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the best supplier product: %1', $exception->getMessage()),
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
