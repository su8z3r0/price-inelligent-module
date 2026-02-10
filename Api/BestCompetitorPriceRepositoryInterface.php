<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Cyper\PriceIntelligent\Model\BestCompetitorPrices;

interface BestCompetitorPriceRepositoryInterface
{
    /**
     * Save best competitor price
     *
     * @param \Cyper\PriceIntelligent\Model\BestCompetitorPrices $bestCompetitorPrice
     * @return \Cyper\PriceIntelligent\Model\BestCompetitorPrices
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(BestCompetitorPrices $bestCompetitorPrice);

    /**
     * Retrieve best competitor price
     *
     * @param int $id
     * @return \Cyper\PriceIntelligent\Model\BestCompetitorPrices
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($id);

    /**
     * Retrieve best competitor prices matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete best competitor price
     *
     * @param \Cyper\PriceIntelligent\Model\BestCompetitorPrices $bestCompetitorPrice
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(BestCompetitorPrices $bestCompetitorPrice);

    /**
     * Delete best competitor price by ID
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
