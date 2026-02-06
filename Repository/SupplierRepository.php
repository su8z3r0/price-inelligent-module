<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Repository;

use Cyper\PriceIntelligent\Api\SupplierRepositoryInterface;
use Cyper\PriceIntelligent\Api\Data\SupplierInterface;
use Cyper\PriceIntelligent\Model\ResourceModel\Supplier as SupplierResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Supplier Repository
 */
class SupplierRepository implements SupplierRepositoryInterface
{
    /**
     * @param SupplierFactory $supplierFactory
     * @param SupplierResource $supplierResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly SupplierFactory $supplierFactory,
        private readonly SupplierResource $supplierResource,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * @inheritDoc
     */
    public function save(SupplierInterface $supplier): SupplierInterface
    {
        try {
            $this->supplierResource->save($supplier);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save supplier', [
                'error' => $e->getMessage(),
                'supplier_id' => $supplier->getSupplierId()
            ]);
            throw new CouldNotSaveException(
                __('Could not save supplier: %1', $e->getMessage()),
                $e
            );
        }
        return $supplier;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $supplierId): SupplierInterface
    {
        $supplier = $this->supplierFactory->create();
        $this->supplierResource->load($supplier, $supplierId);
        
        if (!$supplier->getSupplierId()) {
            throw new NoSuchEntityException(
                __('Supplier with id "%1" does not exist.', $supplierId)
            );
        }
        
        return $supplier;
    }

    /**
     * @inheritDoc
     */
    public function delete(SupplierInterface $supplier): bool
    {
        try {
            $this->supplierResource->delete($supplier);
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete supplier', [
                'error' => $e->getMessage(),
                'supplier_id' => $supplier->getSupplierId()
            ]);
            throw new CouldNotDeleteException(
                __('Could not delete supplier: %1', $e->getMessage()),
                $e
            );
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $supplierId): bool
    {
        return $this->delete($this->getById($supplierId));
    }
}
