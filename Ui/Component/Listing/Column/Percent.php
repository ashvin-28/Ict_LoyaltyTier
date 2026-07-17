<?php

namespace Ict\LoyaltyTier\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class Percent extends Column
{
    /**
     * Prepare percent column.
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

            $item[$fieldName] = $this->formatPercent((float) $item[$fieldName]);
        }

        return $dataSource;
    }

    /**
     * Format a percent value.
     *
     * @param float $value
     * @return string
     */
    private function formatPercent(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') . '%';
    }
}
