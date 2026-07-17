<?php

namespace Ict\LoyaltyTier\Ui\Component;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * Get grid data.
     *
     * @return array
     */
    public function getData()
    {
        $collection = $this->getCollection();
        return [
            'totalRecords' => $collection->getSize(),
            'items' => array_values($collection->toArray()['items'] ?? [])
        ];
    }
}
