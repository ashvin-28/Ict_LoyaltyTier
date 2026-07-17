<?php
namespace Ict\LoyaltyTier\Model\ResourceModel;

use Ict\LoyaltyTier\Model\ResourceModel\Exam\CollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Exam extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var CollectionFactory
     */
    protected $examCollectionFactory;

    /**
     * @param Context $context
     * @param CollectionFactory $examCollectionFactory
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        CollectionFactory $examCollectionFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->examCollectionFactory = $examCollectionFactory;
    }

    /**
     * Resource initialization
     * @return void
     */
    protected function _construct()
    {
        // Table name and primary key
        $this->_init("loyalty", "entity_id");
    }

    /**
     * @param float|int $lifetimeSpend
     * @param bool $activeOnly
     * @return \Ict\LoyaltyTier\Model\ResourceModel\Exam\Collection
     */
    public function getHighestEligibleTier($lifetimeSpend, bool $activeOnly = true)
    {
        $collection = $this->examCollectionFactory->create();
        if ($activeOnly) {
            $collection->addFieldToFilter('status', 1);
        }
        $collection->addFieldToFilter('minimum_spend', ['lteq' => $lifetimeSpend]);
        $collection->setOrder('minimum_spend', 'DESC');
        $collection->setPageSize(1);

        // return $collection;
        return $collection->getFirstItem();
    }

    /**
     * @param string $name
     * @param bool $activeOnly
     * @return \Ict\LoyaltyTier\Model\Exam
     */
    public function getTierByName(string $name, bool $activeOnly = true)
    {
        $collection = $this->examCollectionFactory->create();
        if ($activeOnly) {
            $collection->addFieldToFilter('status', 1);
        }
        $collection->addFieldToFilter('name', $name);
        $collection->setPageSize(1);

        return $collection->getFirstItem();
    }
}
