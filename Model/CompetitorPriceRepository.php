<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model;

use Cyper\PriceIntelligent\Api\CompetitorPriceRepositoryInterface;
use Cyper\PriceIntelligent\Model\ResourceModel\CompetitorPrices as ResourceModel;
use Cyper\PriceIntelligent\Model\ResourceModel\CompetitorPrices\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class CompetitorPriceRepository implements CompetitorPriceRepositoryInterface
{
    /**
     * @var ResourceModel
     */
    protected $resource;

    /**
     * @var CompetitorPricesFactory
     */
    protected $competitorPricesFactory;

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
     * @param CompetitorPricesFactory $competitorPricesFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ResourceModel $resource,
        CompetitorPricesFactory $competitorPricesFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->resource = $resource;
        $this->competitorPricesFactory = $competitorPricesFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(CompetitorPrices $competitorPrice)
    {
        try {
            $this->resource->save($competitorPrice);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the competitor price: %1', $exception->getMessage()),
                $exception
            );
        }
        return $competitorPrice;
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        $competitorPrice = $this->competitorPricesFactory->create();
        $this->resource->load($competitorPrice, $id);
        if (!$competitorPrice->getId()) {
            throw new NoSuchEntityException(__('Competitor price with id "%1" does not exist.', $id));
        }
        return $competitorPrice;
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
    public function delete(CompetitorPrices $competitorPrice)
    {
        try {
            $this->resource->delete($competitorPrice);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the competitor price: %1', $exception->getMessage()),
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
