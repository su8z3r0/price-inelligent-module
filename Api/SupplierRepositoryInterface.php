<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Api;

use Cyper\PriceIntelligent\Api\Data\SupplierInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Supplier Repository Interface
 */
interface SupplierRepositoryInterface
{
    /**
     * Save supplier
     *
     * @param SupplierInterface $supplier
     * @return SupplierInterface
     * @throws CouldNotSaveException
     */
    public function save(SupplierInterface $supplier): SupplierInterface;

    /**
     * Get supplier by ID
     *
     * @param int $supplierId
     * @return SupplierInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $supplierId): SupplierInterface;

    /**
     * Delete supplier
     *
     * @param SupplierInterface $supplier
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(SupplierInterface $supplier): bool;

    /**
     * Delete supplier by ID
     *
     * @param int $supplierId
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $supplierId): bool;
}
