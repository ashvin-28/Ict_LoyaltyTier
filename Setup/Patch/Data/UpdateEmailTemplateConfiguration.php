<?php

namespace Ict\LoyaltyTier\Setup\Patch\Data;

use Ict\LoyaltyTier\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateEmailTemplateConfiguration implements DataPatchInterface
{
    private const OLD_ACHIEVEMENT_TEMPLATE = 'ict_loyaltytier_achievement_email_template';

    private const NEW_ACHIEVEMENT_TEMPLATE = 'loyaltytier_email_achievement_template';

    private const OLD_ENCOURAGEMENT_TEMPLATE = 'ict_loyaltytier_encouragement_email_template';

    private const NEW_ENCOURAGEMENT_TEMPLATE = 'loyaltytier_email_encouragement_template';

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
     * Update default email template config IDs.
     *
     * @return $this
     */
    public function apply()
    {
        $this->updateTemplateValue(
            Config::XML_PATH_ACHIEVEMENT_TEMPLATE,
            self::OLD_ACHIEVEMENT_TEMPLATE,
            self::NEW_ACHIEVEMENT_TEMPLATE
        );
        $this->updateTemplateValue(
            Config::XML_PATH_ENCOURAGEMENT_TEMPLATE,
            self::OLD_ENCOURAGEMENT_TEMPLATE,
            self::NEW_ENCOURAGEMENT_TEMPLATE
        );

        return $this;
    }

    /**
     * Get patch dependencies.
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [
            SetDefaultConfiguration::class,
        ];
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
     * Update value when it is missing or still points to the legacy template id.
     *
     * @param string $path
     * @param string $oldValue
     * @param string $newValue
     * @return void
     */
    private function updateTemplateValue(string $path, string $oldValue, string $newValue): void
    {
        $currentValue = (string) $this->scopeConfig->getValue(
            $path,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        if ($currentValue !== '' && $currentValue !== $oldValue) {
            return;
        }

        $this->configWriter->save($path, $newValue, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
    }
}
