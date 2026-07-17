<?php

namespace Ict\LoyaltyTier\Block\Adminhtml\Exam\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Save implements ButtonProviderInterface
{
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
