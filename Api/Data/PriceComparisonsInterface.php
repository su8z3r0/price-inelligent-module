<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Api\Data;

/**
 * Price Comparisons Data Interface
 */
interface PriceComparisonsInterface
{
    const ID = 'id';
    const SKU = 'sku';
    const EAN = 'ean';
    const NORMALIZED_SKU = 'normalized_sku';
    const PRODUCT_TITLE = 'product_title';
    const OUR_PRICE = 'our_price';
    const COMPETITOR_PRICE = 'competitor_price';
    const PRICE_DIFFERENCE = 'price_difference';
    const IS_COMPETITIVE = 'is_competitive';
    const COMPETITIVENESS_PERCENTAGE = 'competitiveness_percentage';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId(int $id): self;

    /**
     * Get SKU
     *
     * @return string|null
     */
    public function getSku(): ?string;

    /**
     * Set SKU
     *
     * @param string $sku
     * @return $this
     */
    public function setSku(string $sku): self;

    /**
     * Get EAN
     *
     * @return string|null
     */
    public function getEan(): ?string;

    /**
     * Set EAN
     *
     * @param string|null $ean
     * @return $this
     */
    public function setEan(?string $ean): self;

    /**
     * Get normalized SKU
     *
     * @return string|null
     */
    public function getNormalizedSku(): ?string;

    /**
     * Set normalized SKU
     *
     * @param string|null $normalizedSku
     * @return $this
     */
    public function setNormalizedSku(?string $normalizedSku): self;

    /**
     * Get product title
     *
     * @return string|null
     */
    public function getProductTitle(): ?string;

    /**
     * Set product title
     *
     * @param string $title
     * @return $this
     */
    public function setProductTitle(string $title): self;

    /**
     * Get our price
     *
     * @return float
     */
    public function getOurPrice(): float;

    /**
     * Set our price
     *
     * @param float $price
     * @return $this
     */
    public function setOurPrice(float $price): self;

    /**
     * Get competitor price
     *
     * @return float
     */
    public function getCompetitorPrice(): float;

    /**
     * Set competitor price
     *
     * @param float $price
     * @return $this
     */
    public function setCompetitorPrice(float $price): self;

    /**
     * Get price difference
     *
     * @return float
     */
    public function getPriceDifference(): float;

    /**
     * Set price difference
     *
     * @param float $difference
     * @return $this
     */
    public function setPriceDifference(float $difference): self;

    /**
     * Get is competitive flag
     *
     * @return bool
     */
    public function getIsCompetitive(): bool;

    /**
     * Set is competitive flag
     *
     * @param bool $isCompetitive
     * @return $this
     */
    public function setIsCompetitive(bool $isCompetitive): self;

    /**
     * Get competitiveness percentage
     *
     * @return float
     */
    public function getCompetitivenessPercentage(): float;

    /**
     * Set competitiveness percentage
     *
     * @param float $percentage
     * @return $this
     */
    public function setCompetitivenessPercentage(float $percentage): self;

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
