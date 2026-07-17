<?php
namespace Ict\LoyaltyTier\Model\ResourceModel;

class Exam extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Dependency Initilization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init("loyalty", "entity_id");
    }

    public function getHighestEligibleTier($lifetimeSpend)
    {
        $spend = $lifetimeSpend;

        $collection = $this->examCollectionFactory->create();

        $collection->addFieldToFilter('minimum_spend', ['lteq' => $spend]);

        $collection->setOrder('minimum_spend', 'DESC');
    
        $collection->setPageSize(1);

        return $collection;
    }
}
