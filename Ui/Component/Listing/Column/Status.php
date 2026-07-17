<?php

namespace Ict\LoyaltyTier\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class Status extends Column
{
    /**
     * Prepare status column.
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
            if (!isset($item[$fieldName])) {
                continue;
            }

            $item[$fieldName] = ((int) $item[$fieldName]) === 1 ? __('Active') : __('Inactive');
        }

        return $dataSource;
    }
}
