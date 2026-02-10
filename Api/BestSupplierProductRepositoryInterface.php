<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Cyper\PriceIntelligent\Model\BestSupplierProducts;

interface BestSupplierProductRepositoryInterface
{
    /**
     * Save best supplier product
     *
     * @param \Cyper\PriceIntelligent\Model\BestSupplierProducts $bestSupplierProduct
     * @return \Cyper\PriceIntelligent\Model\BestSupplierProducts
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(BestSupplierProducts $bestSupplierProduct);

    /**
     * Retrieve best supplier product
     *
     * @param int $id
     * @return \Cyper\PriceIntelligent\Model\BestSupplierProducts
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($id);

    /**
     * Retrieve best supplier products matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete best supplier product
     *
     * @param \Cyper\PriceIntelligent\Model\BestSupplierProducts $bestSupplierProduct
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(BestSupplierProducts $bestSupplierProduct);

    /**
     * Delete best supplier product by ID
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
