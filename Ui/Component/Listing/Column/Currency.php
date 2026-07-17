<?php

namespace Ict\LoyaltyTier\Ui\Component\Listing\Column;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Currency extends Column
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceCurrency,
        array $components = [],
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare currency column.
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
            if (!isset($item[$fieldName]) || $item[$fieldName] === '') {
                continue;
            }

            $item[$fieldName] = $this->priceCurrency->format(
                (float) $item[$fieldName],
                false,
                PriceCurrencyInterface::DEFAULT_PRECISION
            );
        }

        return $dataSource;
    }
}
