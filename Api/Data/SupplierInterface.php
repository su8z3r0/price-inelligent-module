<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Api\Data;

/**
 * Supplier Data Interface
 */
interface SupplierInterface
{
    const SUPPLIER_ID = 'supplier_id';
    const NAME = 'name';
    const SOURCE_CONFIG = 'source_config';
    const IS_ACTIVE = 'is_active';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get supplier ID
     *
     * @return int|null
     */
    public function getSupplierId(): ?int;

    /**
     * Set supplier ID
     *
     * @param int $supplierId
     * @return $this
     */
    public function setSupplierId(int $supplierId): self;

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self;

    /**
     * Get source config
     *
     * @return array|null
     */
    public function getSourceConfig(): ?array;

    /**
     * Set source config
     *
     * @param array $config
     * @return $this
     */
    public function setSourceConfig(array $config): self;

    /**
     * Get is active flag
     *
     * @return bool
     */
    public function getIsActive(): bool;

    /**
     * Set is active flag
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive(bool $isActive): self;

    /**
     * Get created at
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Get updated at
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;
}
