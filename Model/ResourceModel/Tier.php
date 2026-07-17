<?php

namespace Ict\LoyaltyTier\Model\ResourceModel;

use Ict\LoyaltyTier\Model\ResourceModel\Tier\CollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Tier extends AbstractDb
{
    /**
     * @var CollectionFactory
     */
    private $tierCollectionFactory;

    /**
     * @param Context $context
     * @param CollectionFactory $tierCollectionFactory
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        CollectionFactory $tierCollectionFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->tierCollectionFactory = $tierCollectionFactory;
    }

    /**
     * Resource initialization.
     *
     * @return void
     */
    // phpcs:ignore MEQP2.PHP.ProtectedClassMember.FoundProtected
    protected function _construct()
    {
        $this->_init('loyalty', 'entity_id');
    }

    /**
     * Get highest eligible tier.
     *
     * @param float|int $lifetimeSpend
     * @param bool $activeOnly
     * @return \Ict\LoyaltyTier\Model\Tier
     */
    public function getHighestEligibleTier($lifetimeSpend, bool $activeOnly = true)
    {
        $collection = $this->tierCollectionFactory->create();
        if ($activeOnly) {
            $collection->addFieldToFilter('status', 1);
        }
        $collection->addFieldToFilter('minimum_spend', ['lteq' => $lifetimeSpend]);
        $collection->setOrder('minimum_spend', 'DESC');
        $collection->setPageSize(1);
        $collection->load();
        $items = $collection->getItems();
        $item = reset($items);

        return $item ?: $collection->getNewEmptyItem();
    }

    /**
     * Get tier by name.
     *
     * @param string $name
     * @param bool $activeOnly
     * @return \Ict\LoyaltyTier\Model\Tier
     */
    public function getTierByName(string $name, bool $activeOnly = true)
    {
        $collection = $this->tierCollectionFactory->create();
        if ($activeOnly) {
            $collection->addFieldToFilter('status', 1);
        }
        $collection->addFieldToFilter('name', $name);
        $collection->setPageSize(1);
        $collection->load();
        $items = $collection->getItems();
        $item = reset($items);

        return $item ?: $collection->getNewEmptyItem();
    }
}
