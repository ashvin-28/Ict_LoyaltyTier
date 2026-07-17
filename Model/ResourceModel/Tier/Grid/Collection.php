<?php

namespace Ict\LoyaltyTier\Model\ResourceModel\Tier\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

/**
 * Collection class for the Tier Grid data source.
 */
class Collection extends SearchResult
{
    /**
     * Set up custom filters or mappings if necessary.
     *
     * This is useful if your grid columns don't match table column names exactly.
     *
     * @return $this
     */
    // phpcs:ignore MEQP2.PHP.ProtectedClassMember.FoundProtected
    protected function _initSelect()
    {
        parent::_initSelect();
        return $this;
    }
}
