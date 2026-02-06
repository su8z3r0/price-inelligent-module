<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Repository;

use Cyper\PriceIntelligent\Api\CompetitorRepositoryInterface;
use Cyper\PriceIntelligent\Api\Data\CompetitorInterface;
use Cyper\PriceIntelligent\Model\ResourceModel\Competitor as CompetitorResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Competitor Repository
 */
class CompetitorRepository implements CompetitorRepositoryInterface
{
    /**
     * @param CompetitorFactory $competitorFactory
     * @param CompetitorResource $competitorResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly CompetitorFactory $competitorFactory,
        private readonly CompetitorResource $competitorResource,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * @inheritDoc
     */
    public function save(CompetitorInterface $competitor): CompetitorInterface
    {
        try {
            $this->competitorResource->save($competitor);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save competitor', [
                'error' => $e->getMessage(),
                'competitor_id' => $competitor->getCompetitorId()
            ]);
            throw new CouldNotSaveException(
                __('Could not save competitor: %1', $e->getMessage()),
                $e
            );
        }
        return $competitor;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $competitorId): CompetitorInterface
    {
        $competitor = $this->competitorFactory->create();
        $this->competitorResource->load($competitor, $competitorId);
        
        if (!$competitor->getCompetitorId()) {
            throw new NoSuchEntityException(
                __('Competitor with id "%1" does not exist.', $competitorId)
            );
        }
        
        return $competitor;
    }

    /**
     * @inheritDoc
     */
    public function delete(CompetitorInterface $competitor): bool
    {
        try {
            $this->competitorResource->delete($competitor);
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete competitor', [
                'error' => $e->getMessage(),
                'competitor_id' => $competitor->getCompetitorId()
            ]);
            throw new CouldNotDeleteException(
                __('Could not delete competitor: %1', $e->getMessage()),
                $e
            );
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $competitorId): bool
    {
        return $this->delete($this->getById($competitorId));
    }
}
