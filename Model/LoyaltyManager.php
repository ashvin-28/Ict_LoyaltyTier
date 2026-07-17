<?php

namespace Ict\LoyaltyTier\Model;

use Ict\LoyaltyTier\Model\ResourceModel\Tier as TierResource;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Psr\Log\LoggerInterface;

class LoyaltyManager
{
    private const ACTIVE_STATUS = 1;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var TierResource
     */
    private $tierResource;

    /**
     * @var TierFactory
     */
    private $tierFactory;

    /**
     * @var IndexerRegistry|null
     */
    private $indexerRegistry;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param TierResource $tierResource
     * @param TierFactory $tierFactory
     * @param IndexerRegistry|null $indexerRegistry
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        OrderCollectionFactory $orderCollectionFactory,
        TierResource $tierResource,
        TierFactory $tierFactory,
        ?IndexerRegistry $indexerRegistry = null,
        ?LoggerInterface $logger = null
    ) {
        $this->customerRepository = $customerRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->tierResource = $tierResource;
        $this->tierFactory = $tierFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->logger = $logger;
    }

    /**
     * Get customer lifetime spend.
     *
     * @param int $customerId
     * @return float
     */
    public function getCustomerLifetimeSpend(int $customerId): float
    {
        if ($customerId <= 0) {
            return 0.0;
        }

        $orders = $this->orderCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('state', ['neq' => Order::STATE_CANCELED]);

        $lifetimeSpend = 0.0;
        foreach ($orders as $order) {
            $lifetimeSpend += (float) $order->getBaseGrandTotal();
        }

        return $lifetimeSpend;
    }

    /**
     * Get highest active eligible tier.
     *
     * @param float $lifetimeSpend
     * @return Tier|null
     */
    public function getHighestActiveEligibleTier(float $lifetimeSpend): ?Tier
    {
        $tier = $this->tierResource->getHighestEligibleTier($lifetimeSpend, true);

        return $tier && $tier->getId() ? $tier : null;
    }

    /**
     * Get active tier by ID.
     *
     * @param int $tierId
     * @return Tier|null
     */
    public function getActiveTierById(int $tierId): ?Tier
    {
        if ($tierId <= 0) {
            return null;
        }

        /** @var Tier $tier */
        $tier = $this->tierFactory->create()->load($tierId);
        if (!$tier->getId() || (int) $tier->getData('status') !== self::ACTIVE_STATUS) {
            return null;
        }

        return $tier;
    }

    /**
     * Get customer discount tier.
     *
     * @param CustomerInterface $customer
     * @return Tier|null
     */
    public function getCustomerDiscountTier(CustomerInterface $customer): ?Tier
    {
        $customerId = (int) $customer->getId();
        if ($customerId > 0) {
            $tier = $this->getHighestActiveEligibleTier($this->getCustomerLifetimeSpend($customerId));
            if ($tier) {
                return $tier;
            }
        }

        $tierId = (int) $this->getCustomerAttributeValue($customer, 'loyalty_tier_id');
        if ($tierId > 0) {
            $tier = $this->getActiveTierById($tierId);
            if ($tier) {
                return $tier;
            }
        }

        $tierName = trim((string) $this->getCustomerAttributeValue($customer, 'loyalty_tier'));
        if ($tierName !== '') {
            $tier = $this->tierResource->getTierByName($tierName, true);
            if ($tier && $tier->getId()) {
                return $tier;
            }
        }

        return null;
    }

    /**
     * Check whether customer can use tier discount.
     *
     * @param CustomerInterface $customer
     * @param Tier $tier
     * @return bool
     */
    public function canUseTierDiscount(CustomerInterface $customer, Tier $tier): bool
    {
        $limit = (int) $tier->getData('limit');
        if ($limit <= 0) {
            return true;
        }

        $customerId = (int) $customer->getId();
        if ($customerId <= 0) {
            return false;
        }

        return $this->getTierUsageCountForCustomer($customerId, (int) $tier->getId()) < $limit;
    }

    /**
     * Get remaining tier usage for customer.
     *
     * @param int $customerId
     * @param Tier $tier
     * @return int|null
     */
    public function getRemainingUsageForCustomer(int $customerId, Tier $tier)
    {
        $limit = (int) $tier->getData('limit');
        if ($limit <= 0) {
            return null;
        }

        return max(0, $limit - $this->getTierUsageCountForCustomer($customerId, (int) $tier->getId()));
    }

    /**
     * Get tier usage count for customer.
     *
     * @param int $customerId
     * @param int $tierId
     * @return int
     */
    public function getTierUsageCountForCustomer(int $customerId, int $tierId): int
    {
        if ($customerId <= 0 || $tierId <= 0) {
            return 0;
        }

        $orders = $this->orderCollectionFactory->create()
            ->addFieldToSelect('entity_id')
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('loyalty_tier_id', $tierId)
            ->addFieldToFilter('base_loyalty_discount_amount', ['gt' => 0])
            ->addFieldToFilter('state', ['neq' => Order::STATE_CANCELED]);

        return (int) $orders->getSize();
    }

    /**
     * Sync customer loyalty tier data.
     *
     * @param int $customerId
     * @return void
     */
    public function syncCustomerTier(int $customerId): void
    {
        if ($customerId <= 0) {
            return;
        }

        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $exception) {
            return;
        }

        $lifetimeSpend = $this->getCustomerLifetimeSpend($customerId);
        $tier = $this->getHighestActiveEligibleTier($lifetimeSpend);

        $customer->setCustomAttribute('lifetime_spend', $lifetimeSpend);

        if ($tier) {
            $customer->setCustomAttribute('loyalty_tier_id', (int) $tier->getId());
            $customer->setCustomAttribute('loyalty_tier', (string) $tier->getName());
        } else {
            $customer->setCustomAttribute('loyalty_tier_id', null);
            $customer->setCustomAttribute('loyalty_tier', '');
        }

        $this->customerRepository->save($customer);
        $this->reindexCustomerGridRow($customerId);
    }

    /**
     * Get customer custom attribute value.
     *
     * @param CustomerInterface $customer
     * @param string $attributeCode
     * @return mixed|null
     */
    private function getCustomerAttributeValue(CustomerInterface $customer, string $attributeCode)
    {
        $attribute = $customer->getCustomAttribute($attributeCode);

        return $attribute ? $attribute->getValue() : null;
    }

    /**
     * Reindex customer grid row.
     *
     * @param int $customerId
     * @return void
     */
    private function reindexCustomerGridRow(int $customerId): void
    {
        try {
            $this->getIndexerRegistry()
                ->get(CustomerModel::CUSTOMER_GRID_INDEXER_ID)
                ->reindexRow($customerId);
        } catch (\Throwable $exception) {
            $this->getLogger()->error(
                'Unable to reindex customer loyalty grid row.',
                ['customer_id' => $customerId, 'exception' => $exception]
            );
        }
    }

    /**
     * Get indexer registry.
     *
     * @return IndexerRegistry
     */
    private function getIndexerRegistry(): IndexerRegistry
    {
        if (!$this->indexerRegistry) {
            $this->indexerRegistry = ObjectManager::getInstance()->get(IndexerRegistry::class);
        }

        return $this->indexerRegistry;
    }

    /**
     * Get logger.
     *
     * @return LoggerInterface
     */
    private function getLogger(): LoggerInterface
    {
        if (!$this->logger) {
            $this->logger = ObjectManager::getInstance()->get(LoggerInterface::class);
        }

        return $this->logger;
    }
}
