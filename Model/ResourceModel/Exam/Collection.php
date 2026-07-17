<?php

namespace Ict\LoyaltyTier\Model\ResourceModel\Exam;

use Ict\LoyaltyTier\Model\Exam;
use Ict\LoyaltyTier\Model\ResourceModel\Exam as ExamResource;
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
            Exam::class,
            ExamResource::class
        );
    }
}
