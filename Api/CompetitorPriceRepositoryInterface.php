<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Cyper\PriceIntelligent\Model\CompetitorPrices;

interface CompetitorPriceRepositoryInterface
{
    /**
     * Save competitor price
     *
     * @param \Cyper\PriceIntelligent\Model\CompetitorPrices $competitorPrice
     * @return \Cyper\PriceIntelligent\Model\CompetitorPrices
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(CompetitorPrices $competitorPrice);

    /**
     * Retrieve competitor price
     *
     * @param int $id
     * @return \Cyper\PriceIntelligent\Model\CompetitorPrices
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($id);

    /**
     * Retrieve competitor prices matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete competitor price
     *
     * @param \Cyper\PriceIntelligent\Model\CompetitorPrices $competitorPrice
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(CompetitorPrices $competitorPrice);

    /**
     * Delete competitor price by ID
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
