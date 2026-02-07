<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Api;

use Cyper\PriceIntelligent\Api\Data\CompetitorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Competitor Repository Interface
 */
interface CompetitorRepositoryInterface
{
    /**
     * Save competitor
     *
     * @param CompetitorInterface $competitor
     * @return CompetitorInterface
     * @throws CouldNotSaveException
     */
    public function save(CompetitorInterface $competitor): CompetitorInterface;

    /**
     * Get competitor by ID
     *
     * @param int $competitorId
     * @return CompetitorInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $competitorId): CompetitorInterface;

    /**
     * Delete competitor
     *
     * @param CompetitorInterface $competitor
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(CompetitorInterface $competitor): bool;

    /**
     * Delete competitor by ID
     *
     * @param int $competitorId
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $competitorId): bool;
}
