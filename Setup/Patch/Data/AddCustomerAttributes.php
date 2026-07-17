<?php

namespace Ict\LoyaltyTier\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddCustomerAttributes implements DataPatchInterface
{
    private $moduleDataSetup;
    private $customerSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);



        $this->addAttributeIfMissing(
            $customerSetup,
            'loyalty_tier',
            [
                'type' => 'varchar',
                'label' => 'Tier Name',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'position' => 1000,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => false,
                'is_searchable_in_grid' => false,

            ],
            ['adminhtml_customer']
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }


    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [
            'Ict\Exam\Setup\Patch\Data\AddCustomerAttributes',
        ];
    }

    private function addAttributeIfMissing(
        \Magento\Customer\Setup\CustomerSetup $customerSetup,
        string $attributeCode,
        array $attributeData,
        array $forms
    ): void {
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $attributeCode);
        if (!$attribute || !$attribute->getId()) {
            $customerSetup->addAttribute(Customer::ENTITY, $attributeCode, $attributeData);
            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $attributeCode);
        }

        if (!$attribute || !$attribute->getId()) {
            return;
        }

        $attribute->setData('used_in_forms', $forms);
        $attribute->save();
    }
}
