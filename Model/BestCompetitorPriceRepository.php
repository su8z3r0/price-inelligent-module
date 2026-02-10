<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model;

use Cyper\PriceIntelligent\Api\BestCompetitorPriceRepositoryInterface;
use Cyper\PriceIntelligent\Model\ResourceModel\BestCompetitorPrices as ResourceModel;
use Cyper\PriceIntelligent\Model\ResourceModel\BestCompetitorPrices\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class BestCompetitorPriceRepository implements BestCompetitorPriceRepositoryInterface
{
    /**
     * @var ResourceModel
     */
    protected $resource;

    /**
     * @var BestCompetitorPricesFactory
     */
    protected $bestCompetitorPricesFactory;

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
     * @param BestCompetitorPricesFactory $bestCompetitorPricesFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ResourceModel $resource,
        BestCompetitorPricesFactory $bestCompetitorPricesFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->resource = $resource;
        $this->bestCompetitorPricesFactory = $bestCompetitorPricesFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(BestCompetitorPrices $bestCompetitorPrice)
    {
        try {
            $this->resource->save($bestCompetitorPrice);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the best competitor price: %1', $exception->getMessage()),
                $exception
            );
        }
        return $bestCompetitorPrice;
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        $bestCompetitorPrice = $this->bestCompetitorPricesFactory->create();
        $this->resource->load($bestCompetitorPrice, $id);
        if (!$bestCompetitorPrice->getId()) {
            throw new NoSuchEntityException(__('Best competitor price with id "%1" does not exist.', $id));
        }
        return $bestCompetitorPrice;
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
    public function delete(BestCompetitorPrices $bestCompetitorPrice)
    {
        try {
            $this->resource->delete($bestCompetitorPrice);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the best competitor price: %1', $exception->getMessage()),
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
