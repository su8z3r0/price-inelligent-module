<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Model;

use Cyper\PriceIntelligent\Api\Data\CompetitorInterface;
use Magento\Framework\Model\AbstractModel;

class Competitor extends AbstractModel implements CompetitorInterface
{
    protected $_eventPrefix = 'cyper_competitor';
    protected $_eventObject = 'competitor';
    
    protected function _construct()
    {
        $this->_init(\Cyper\PriceIntelligent\Model\ResourceModel\Competitor::class);
    }

    /**
     * @inheritDoc
     */
    public function getCompetitorId(): ?int
    {
        $id = $this->getData(self::COMPETITOR_ID);
        return $id ? (int)$id : null;
    }

    /**
     * @inheritDoc
     */
    public function setCompetitorId(int $competitorId): CompetitorInterface
    {
        return $this->setData(self::COMPETITOR_ID, $competitorId);
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
    public function setName(string $name): CompetitorInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getWebsiteUrl(): ?string
    {
        return $this->getData(self::WEBSITE_URL);
    }

    /**
     * @inheritDoc
     */
    public function setWebsiteUrl(string $url): CompetitorInterface
    {
        return $this->setData(self::WEBSITE_URL, $url);
    }

    /**
     * @inheritDoc
     */
    public function getScrapingConfig(): ?array
    {
        $config = $this->getData(self::SCRAPING_CONFIG);
        if (is_string($config)) {
            $config = json_decode($config, true);
        }
        return is_array($config) ? $config : null;
    }

    /**
     * @inheritDoc
     */
    public function setScrapingConfig(array $config): CompetitorInterface
    {
        return $this->setData(self::SCRAPING_CONFIG, $config);
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
    public function setIsActive(bool $isActive): CompetitorInterface
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
