<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Api;

use Cyper\PriceIntelligent\Api\Data\PriceComparisonsInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Price Comparisons Repository Interface
 */
interface PriceComparisonsRepositoryInterface
{
    /**
     * Save price comparison
     *
     * @param PriceComparisonsInterface $priceComparison
     * @return PriceComparisonsInterface
     * @throws CouldNotSaveException
     */
    public function save(PriceComparisonsInterface $priceComparison): PriceComparisonsInterface;

    /**
     * Get price comparison by ID
     *
     * @param int $id
     * @return PriceComparisonsInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): PriceComparisonsInterface;

    /**
     * Delete price comparison
     *
     * @param PriceComparisonsInterface $priceComparison
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(PriceComparisonsInterface $priceComparison): bool;

    /**
     * Delete price comparison by ID
     *
     * @param int $id
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $id): bool;
}
