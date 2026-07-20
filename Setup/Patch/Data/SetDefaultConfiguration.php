<?php

namespace Ict\LoyaltyTier\Setup\Patch\Data;

use Ict\LoyaltyTier\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class SetDefaultConfiguration implements DataPatchInterface
{
    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param WriterInterface $configWriter
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Set default module configuration.
     *
     * @return $this
     */
    public function apply()
    {
        $this->saveIfMissing(Config::XML_PATH_ENABLED, '1');
        $this->saveIfMissing(Config::XML_PATH_FRONTEND_LABEL, 'Loyalty Tier Details');
        $this->saveIfMissing(Config::XML_PATH_ACHIEVEMENT_ENABLED, '0');
        $this->saveIfMissing(Config::XML_PATH_ACHIEVEMENT_SENDER, 'general');
        $this->saveIfMissing(
            Config::XML_PATH_ACHIEVEMENT_TEMPLATE,
            'loyaltytier_email_achievement_template'
        );
        $this->saveIfMissing(Config::XML_PATH_ENCOURAGEMENT_ENABLED, '0');
        $this->saveIfMissing(Config::XML_PATH_ENCOURAGEMENT_SENDER, 'general');
        $this->saveIfMissing(
            Config::XML_PATH_ENCOURAGEMENT_TEMPLATE,
            'loyaltytier_email_encouragement_template'
        );
        $this->saveIfMissing(Config::XML_PATH_ENCOURAGEMENT_THRESHOLD, '90');

        return $this;
    }

    /**
     * Get patch dependencies.
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get patch aliases.
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Save default config value when missing.
     *
     * @param string $path
     * @param string $value
     * @return void
     */
    private function saveIfMissing(string $path, string $value): void
    {
        $currentValue = $this->scopeConfig->getValue(
            $path,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        if ($currentValue !== null) {
            return;
        }

        $this->configWriter->save($path, $value, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
    }
}
