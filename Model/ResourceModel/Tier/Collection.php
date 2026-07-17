<?php

namespace Ict\LoyaltyTier\Model\ResourceModel\Tier;

use Ict\LoyaltyTier\Model\Tier;
use Ict\LoyaltyTier\Model\ResourceModel\Tier as TierResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Dependency Initilization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_setIdFieldName('entity_id');
        $this->_init(
            Tier::class,
            TierResource::class
        );
    }
}
