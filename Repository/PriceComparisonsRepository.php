<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Repository;

use Cyper\PriceIntelligent\Api\PriceComparisonsRepositoryInterface;
use Cyper\PriceIntelligent\Api\Data\PriceComparisonsInterface;
use Cyper\PriceIntelligent\Model\ResourceModel\PriceComparisons as PriceComparisonsResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Price Comparisons Repository
 */
class PriceComparisonsRepository implements PriceComparisonsRepositoryInterface
{
    /**
     * @param PriceComparisonsFactory $priceComparisonsFactory
     * @param PriceComparisonsResource $priceComparisonsResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly PriceComparisonsFactory $priceComparisonsFactory,
        private readonly PriceComparisonsResource $priceComparisonsResource,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * @inheritDoc
     */
    public function save(PriceComparisonsInterface $priceComparison): PriceComparisonsInterface
    {
        try {
            $this->priceComparisonsResource->save($priceComparison);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save price comparison', [
                'error' => $e->getMessage(),
                'id' => $priceComparison->getId()
            ]);
            throw new CouldNotSaveException(
                __('Could not save price comparison: %1', $e->getMessage()),
                $e
            );
        }
        return $priceComparison;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $id): PriceComparisonsInterface
    {
        $priceComparison = $this->priceComparisonsFactory->create();
        $this->priceComparisonsResource->load($priceComparison, $id);
        
        if (!$priceComparison->getId()) {
            throw new NoSuchEntityException(
                __('Price comparison with id "%1" does not exist.', $id)
            );
        }
        
        return $priceComparison;
    }

    /**
     * @inheritDoc
     */
    public function delete(PriceComparisonsInterface $priceComparison): bool
    {
        try {
            $this->priceComparisonsResource->delete($priceComparison);
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete price comparison', [
                'error' => $e->getMessage(),
                'id' => $priceComparison->getId()
            ]);
            throw new CouldNotDeleteException(
                __('Could not delete price comparison: %1', $e->getMessage()),
                $e
            );
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $id): bool
    {
        return $this->delete($this->getById($id));
    }
}
