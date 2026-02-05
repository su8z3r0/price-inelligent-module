<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model;

use Cyper\PriceIntelligent\Api\Data\SupplierInterface;
use Magento\Framework\Model\AbstractModel;

class Supplier extends AbstractModel implements SupplierInterface
{
    protected $_eventPrefix = 'cyper_supplier';
    protected $_eventObject = 'supplier';
    
    protected function _construct()
    {
        $this->_init(\Cyper\PriceIntelligent\Model\ResourceModel\Supplier::class);
    }

    /**
     * @inheritDoc
     */
    public function getSupplierId(): ?int
    {
        $id = $this->getData(self::SUPPLIER_ID);
        return $id ? (int)$id : null;
    }

    /**
     * @inheritDoc
     */
    public function setSupplierId(int $supplierId): SupplierInterface
    {
        return $this->setData(self::SUPPLIER_ID, $supplierId);
    }

    /**
     * @inheritDoc
     */
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): SupplierInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getSourceConfig(): ?array
    {
        $config = $this->getData(self::SOURCE_CONFIG);
        if (is_string($config)) {
            $config = json_decode($config, true);
        }
        return is_array($config) ? $config : null;
    }

    /**
     * @inheritDoc
     */
    public function setSourceConfig(array $config): SupplierInterface
    {
        return $this->setData(self::SOURCE_CONFIG, $config);
    }

    /**
     * @inheritDoc
     */
    public function getIsActive(): bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * @inheritDoc
     */
    public function setIsActive(bool $isActive): SupplierInterface
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }
}
