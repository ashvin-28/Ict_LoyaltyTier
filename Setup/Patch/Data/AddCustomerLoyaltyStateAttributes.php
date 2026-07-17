<?php

namespace Ict\LoyaltyTier\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddCustomerLoyaltyStateAttributes implements DataPatchInterface
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
     * Apply customer loyalty state attributes.
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $this->addAttributeIfMissing(
            $customerSetup,
            'loyalty_tier_id',
            [
                'type' => 'int',
                'label' => 'Loyalty Tier Id',
                'input' => 'text',
                'required' => false,
                'visible' => false,
                'user_defined' => false,
                'position' => 1001,
                'system' => 0,
            ]
        );

        $this->addAttributeIfMissing(
            $customerSetup,
            'lifetime_spend',
            [
                'type' => 'decimal',
                'label' => 'Lifetime Spend',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'position' => 1002,
                'system' => 0,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => false,
                'is_searchable_in_grid' => false,
            ]
        );

        $this->assignForms($customerSetup, 'loyalty_tier', ['adminhtml_customer']);
        $this->assignForms($customerSetup, 'lifetime_spend', ['adminhtml_customer']);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Get patch dependencies.
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [AddCustomerAttributes::class];
    }

    /**
     * Get patch aliases.
     *
     * @return array
     */
    public function getAliases()
    {
        return [
            \Ict\Exam\Setup\Patch\Data\AddCustomerLoyaltyStateAttributes::class,
        ];
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

    /**
     * Assign customer attribute forms.
     *
     * @param CustomerSetup $customerSetup
     * @param string $attributeCode
     * @param array $forms
     * @return void
     */
    private function assignForms(
        CustomerSetup $customerSetup,
        string $attributeCode,
        array $forms
    ): void {
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $attributeCode);
        if (!$attribute || !$attribute->getId()) {
            return;
        }

        $attribute->setData('used_in_forms', $forms);
        $attribute->save();
    }
}
