<?php

namespace Ict\LoyaltyTier\Ui\Component\Listing\Column;

use Ict\LoyaltyTier\Model\LoyaltyManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class CustomerLifetimeSpend extends Column
{
    /**
     * @var LoyaltyManager
     */
    private $loyaltyManager;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param LoyaltyManager $loyaltyManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        LoyaltyManager $loyaltyManager,
        PriceCurrencyInterface $priceCurrency,
        array $components = [],
        array $data = []
    ) {
        $this->loyaltyManager = $loyaltyManager;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare customer lifetime spend column.
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

            $item[$fieldName] = $this->priceCurrency->format(
                $this->loyaltyManager->getCustomerLifetimeSpend($customerId),
                false,
                PriceCurrencyInterface::DEFAULT_PRECISION
            );
        }

        return $dataSource;
    }
}
