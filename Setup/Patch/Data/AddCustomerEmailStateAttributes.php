<?php

namespace Ict\LoyaltyTier\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddCustomerEmailStateAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * Apply customer email state attributes.
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $this->addAttributeIfMissing(
            $customerSetup,
            'loyalty_encouragement_email_tier_id',
            [
                'type' => 'int',
                'label' => 'Loyalty Encouragement Email Tier Id',
                'input' => 'text',
                'required' => false,
                'visible' => false,
                'user_defined' => false,
                'position' => 1003,
                'system' => 0,
            ]
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Get patch dependencies.
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [AddCustomerLoyaltyStateAttributes::class];
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
     * Add attribute when missing.
     *
     * @param CustomerSetup $customerSetup
     * @param string $attributeCode
     * @param array $attributeData
     * @return void
     */
    private function addAttributeIfMissing(
        CustomerSetup $customerSetup,
        string $attributeCode,
        array $attributeData
    ): void {
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $attributeCode);
        if ($attribute && $attribute->getId()) {
            return;
        }

        $customerSetup->addAttribute(Customer::ENTITY, $attributeCode, $attributeData);
    }
}
