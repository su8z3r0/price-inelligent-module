<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Api\Data;

/**
 * Competitor Data Interface
 */
interface CompetitorInterface
{
    const COMPETITOR_ID = 'competitor_id';
    const NAME = 'name';
    const WEBSITE_URL = 'website_url';
    const SCRAPING_CONFIG = 'crawler_config';
    const IS_ACTIVE = 'is_active';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get competitor ID
     *
     * @return int|null
     */
    public function getCompetitorId(): ?int;

    /**
     * Set competitor ID
     *
     * @param int $competitorId
     * @return $this
     */
    public function setCompetitorId(int $competitorId): self;

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
     * Get website URL
     *
     * @return string|null
     */
    public function getWebsiteUrl(): ?string;

    /**
     * Set website URL
     *
     * @param string $url
     * @return $this
     */
    public function setWebsiteUrl(string $url): self;

    /**
     * Get scraping config
     *
     * @return array|null
     */
    public function getScrapingConfig(): ?array;

    /**
     * Set scraping config
     *
     * @param array $config
     * @return $this
     */
    public function setScrapingConfig(array $config): self;

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
