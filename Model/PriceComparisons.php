<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model;

use Cyper\PriceIntelligent\Api\Data\PriceComparisonsInterface;
use Magento\Framework\Model\AbstractModel;

class PriceComparisons extends AbstractModel implements PriceComparisonsInterface
{
    protected function _construct()
    {
        $this->_init(\Cyper\PriceIntelligent\Model\ResourceModel\PriceComparisons::class);
    }

    /**
     * @inheritDoc
     */
    public function getId(): ?int
    {
        $id = $this->getData(self::ID);
        return $id ? (int)$id : null;
    }

    /**
     * @inheritDoc
     */
    public function setId(int $id): PriceComparisonsInterface
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getSku(): ?string
    {
        return $this->getData(self::SKU);
    }

    /**
     * @inheritDoc
     */
    public function setSku(string $sku): PriceComparisonsInterface
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * @inheritDoc
     */
    public function getEan(): ?string
    {
        return $this->getData(self::EAN);
    }

    /**
     * @inheritDoc
     */
    public function setEan(?string $ean): PriceComparisonsInterface
    {
        return $this->setData(self::EAN, $ean);
    }

    /**
     * @inheritDoc
     */
    public function getNormalizedSku(): ?string
    {
        return $this->getData(self::NORMALIZED_SKU);
    }

    /**
     * @inheritDoc
     */
    public function setNormalizedSku(?string $normalizedSku): PriceComparisonsInterface
    {
        return $this->setData(self::NORMALIZED_SKU, $normalizedSku);
    }

    /**
     * @inheritDoc
     */
    public function getProductTitle(): ?string
    {
        return $this->getData(self::PRODUCT_TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setProductTitle(string $title): PriceComparisonsInterface
    {
        return $this->setData(self::PRODUCT_TITLE, $title);
    }

    /**
     * @inheritDoc
     */
    public function getOurPrice(): float
    {
        return (float)$this->getData(self::OUR_PRICE);
    }

    /**
     * @inheritDoc
     */
    public function setOurPrice(float $price): PriceComparisonsInterface
    {
        return $this->setData(self::OUR_PRICE, $price);
    }

    /**
     * @inheritDoc
     */
    public function getCompetitorPrice(): float
    {
        return (float)$this->getData(self::COMPETITOR_PRICE);
    }

    /**
     * @inheritDoc
     */
    public function setCompetitorPrice(float $price): PriceComparisonsInterface
    {
        return $this->setData(self::COMPETITOR_PRICE, $price);
    }

    /**
     * @inheritDoc
     */
    public function getPriceDifference(): float
    {
        return (float)$this->getData(self::PRICE_DIFFERENCE);
    }

    /**
     * @inheritDoc
     */
    public function setPriceDifference(float $difference): PriceComparisonsInterface
    {
        return $this->setData(self::PRICE_DIFFERENCE, $difference);
    }

    /**
     * @inheritDoc
     */
    public function getIsCompetitive(): bool
    {
        return (bool)$this->getData(self::IS_COMPETITIVE);
    }

    /**
     * @inheritDoc
     */
    public function setIsCompetitive(bool $isCompetitive): PriceComparisonsInterface
    {
        return $this->setData(self::IS_COMPETITIVE, $isCompetitive);
    }

    /**
     * @inheritDoc
     */
    public function getCompetitivenessPercentage(): float
    {
        return (float)$this->getData(self::COMPETITIVENESS_PERCENTAGE);
    }

    /**
     * @inheritDoc
     */
    public function setCompetitivenessPercentage(float $percentage): PriceComparisonsInterface
    {
        return $this->setData(self::COMPETITIVENESS_PERCENTAGE, $percentage);
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
