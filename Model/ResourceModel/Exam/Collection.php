<?php
namespace Ict\LoyaltyTier\Model\ResourceModel\Exam;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    
    /**
     * Dependency Initilization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Ict\LoyaltyTier\Model\Exam::class,
            \Ict\LoyaltyTier\Model\ResourceModel\Exam::class
        );
        // $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}
