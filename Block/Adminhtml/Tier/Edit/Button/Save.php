<?php

namespace Ict\LoyaltyTier\Block\Adminhtml\Tier\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Save implements ButtonProviderInterface
{
    /**
     * Get save button data.
     *
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save '),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [[
                            'targetName' => 'ict_loyaltytier_tier_form.ict_loyaltytier_tier_form',
                            'actionName' => 'save',
                            'params' => [false],
                        ]]
                    ]
                ]
            ],
            'sort_order' => 90
        ];
    }
}
