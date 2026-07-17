<?php

namespace Ict\LoyaltyTier\Ui\Component\Listing\Column;

use Ict\LoyaltyTier\Model\LoyaltyManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class CustomerTier extends Column
{
    /**
     * @var LoyaltyManager
     */
    private $loyaltyManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param LoyaltyManager $loyaltyManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        LoyaltyManager $loyaltyManager,
        array $components = [],
        array $data = []
    ) {
        $this->loyaltyManager = $loyaltyManager;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare customer loyalty tier column.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            $customerId = (int) ($item['entity_id'] ?? 0);
            if ($customerId <= 0) {
                continue;
            }

            $tier = $this->loyaltyManager->getHighestActiveEligibleTier(
                $this->loyaltyManager->getCustomerLifetimeSpend($customerId)
            );
            $item[$fieldName] = $tier && $tier->getId() ? (string) $tier->getName() : '';
        }

        return $dataSource;
    }
}
