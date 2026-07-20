<?php

namespace Ict\LoyaltyTier\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const XML_PATH_ENABLED = 'loyaltytier/general/enabled';

    public const XML_PATH_FRONTEND_LABEL = 'loyaltytier/general/frontend_label';

    public const XML_PATH_ACHIEVEMENT_ENABLED = 'loyaltytier/email/enable_achievement_email';

    public const XML_PATH_ACHIEVEMENT_SENDER = 'loyaltytier/email/achievement_sender';

    public const XML_PATH_ACHIEVEMENT_TEMPLATE = 'loyaltytier/email/achievement_template';

    public const XML_PATH_ENCOURAGEMENT_ENABLED = 'loyaltytier/email/enable_encouragement_email';

    public const XML_PATH_ENCOURAGEMENT_SENDER = 'loyaltytier/email/encouragement_sender';

    public const XML_PATH_ENCOURAGEMENT_TEMPLATE = 'loyaltytier/email/encouragement_template';

    public const XML_PATH_ENCOURAGEMENT_THRESHOLD = 'loyaltytier/email/encouragement_threshold';

    private const DEFAULT_FRONTEND_LABEL = 'Loyalty Tier Details';

    private const DEFAULT_ENABLED = true;

    private const DEFAULT_SENDER = 'general';

    private const DEFAULT_ACHIEVEMENT_TEMPLATE = 'loyaltytier_email_achievement_template';

    private const DEFAULT_ENCOURAGEMENT_TEMPLATE = 'loyaltytier_email_encouragement_template';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check whether loyalty tier module features are enabled.
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null): bool
    {
        $value = $this->getValue(self::XML_PATH_ENABLED, $storeId);
        if ($value === null || $value === '') {
            return self::DEFAULT_ENABLED;
        }

        return (bool) $value;
    }

    /**
     * Get frontend loyalty section label.
     *
     * @param int|string|null $storeId
     * @return string
     */
    public function getFrontendLabel($storeId = null): string
    {
        $label = trim((string) $this->getValue(self::XML_PATH_FRONTEND_LABEL, $storeId));

        return $label !== '' ? $label : self::DEFAULT_FRONTEND_LABEL;
    }

    /**
     * Check whether achievement emails are enabled.
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isAchievementEmailEnabled($storeId = null): bool
    {
        return $this->isEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_ACHIEVEMENT_ENABLED,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    /**
     * Get achievement email sender identity.
     *
     * @param int|string|null $storeId
     * @return string
     */
    public function getAchievementSender($storeId = null): string
    {
        return $this->getNonEmptyValue(self::XML_PATH_ACHIEVEMENT_SENDER, self::DEFAULT_SENDER, $storeId);
    }

    /**
     * Get achievement email template identifier.
     *
     * @param int|string|null $storeId
     * @return string
     */
    public function getAchievementTemplate($storeId = null): string
    {
        return $this->getNonEmptyValue(
            self::XML_PATH_ACHIEVEMENT_TEMPLATE,
            self::DEFAULT_ACHIEVEMENT_TEMPLATE,
            $storeId
        );
    }

    /**
     * Check whether encouragement emails are enabled.
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isEncouragementEmailEnabled($storeId = null): bool
    {
        return $this->isEnabled($storeId)
            && $this->scopeConfig->isSetFlag(
                self::XML_PATH_ENCOURAGEMENT_ENABLED,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
    }

    /**
     * Get encouragement email sender identity.
     *
     * @param int|string|null $storeId
     * @return string
     */
    public function getEncouragementSender($storeId = null): string
    {
        return $this->getNonEmptyValue(self::XML_PATH_ENCOURAGEMENT_SENDER, self::DEFAULT_SENDER, $storeId);
    }

    /**
     * Get encouragement email template identifier.
     *
     * @param int|string|null $storeId
     * @return string
     */
    public function getEncouragementTemplate($storeId = null): string
    {
        return $this->getNonEmptyValue(
            self::XML_PATH_ENCOURAGEMENT_TEMPLATE,
            self::DEFAULT_ENCOURAGEMENT_TEMPLATE,
            $storeId
        );
    }

    /**
     * Get encouragement threshold percentage.
     *
     * @param int|string|null $storeId
     * @return float
     */
    public function getEncouragementThreshold($storeId = null): float
    {
        $threshold = (float) $this->getValue(self::XML_PATH_ENCOURAGEMENT_THRESHOLD, $storeId);
        if ($threshold <= 0.0) {
            return 90.0;
        }

        return min(100.0, $threshold);
    }

    /**
     * Get config value.
     *
     * @param string $path
     * @param int|string|null $storeId
     * @return mixed
     */
    private function getValue(string $path, $storeId = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Get non-empty config value.
     *
     * @param string $path
     * @param string $default
     * @param int|string|null $storeId
     * @return string
     */
    private function getNonEmptyValue(string $path, string $default, $storeId = null): string
    {
        $value = trim((string) $this->getValue($path, $storeId));

        return $value !== '' ? $value : $default;
    }
}
