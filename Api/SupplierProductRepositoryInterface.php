<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Cyper\PriceIntelligent\Model\SupplierProducts;

interface SupplierProductRepositoryInterface
{
    /**
     * Save supplier product
     *
     * @param \Cyper\PriceIntelligent\Model\SupplierProducts $supplierProduct
     * @return \Cyper\PriceIntelligent\Model\SupplierProducts
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(SupplierProducts $supplierProduct);

    /**
     * Retrieve supplier product
     *
     * @param int $id
     * @return \Cyper\PriceIntelligent\Model\SupplierProducts
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($id);

    /**
     * Retrieve supplier products matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete supplier product
     *
     * @param \Cyper\PriceIntelligent\Model\SupplierProducts $supplierProduct
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(SupplierProducts $supplierProduct);

    /**
     * Delete supplier product by ID
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
