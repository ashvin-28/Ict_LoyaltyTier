<?php

namespace Ict\LoyaltyTier\Model;

use Ict\LoyaltyTier\Model\ResourceModel\Exam as TierResource;
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
     * @var ExamFactory
     */
    private $tierFactory;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        OrderCollectionFactory $orderCollectionFactory,
        TierResource $tierResource,
        ExamFactory $tierFactory,
        ?IndexerRegistry $indexerRegistry = null,
        ?LoggerInterface $logger = null
    ) {
        $this->customerRepository = $customerRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->tierResource = $tierResource;
        $this->tierFactory = $tierFactory;
        $this->indexerRegistry = $indexerRegistry ?: ObjectManager::getInstance()->get(IndexerRegistry::class);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

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

    public function getHighestActiveEligibleTier(float $lifetimeSpend): ?Exam
    {
        $tier = $this->tierResource->getHighestEligibleTier($lifetimeSpend, true);

        return $tier && $tier->getId() ? $tier : null;
    }

    public function getActiveTierById(int $tierId): ?Exam
    {
        if ($tierId <= 0) {
            return null;
        }

        /** @var Exam $tier */
        $tier = $this->tierFactory->create()->load($tierId);
        if (!$tier->getId() || (int) $tier->getData('status') !== self::ACTIVE_STATUS) {
            return null;
        }

        return $tier;
    }

    public function getCustomerDiscountTier(CustomerInterface $customer): ?Exam
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

    public function canUseTierDiscount(CustomerInterface $customer, Exam $tier): bool
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

    public function getRemainingUsageForCustomer(int $customerId, Exam $tier)
    {
        $limit = (int) $tier->getData('limit');
        if ($limit <= 0) {
            return null;
        }

        return max(0, $limit - $this->getTierUsageCountForCustomer($customerId, (int) $tier->getId()));
    }

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
     * @return mixed|null
     */
    private function getCustomerAttributeValue(CustomerInterface $customer, string $attributeCode)
    {
        $attribute = $customer->getCustomAttribute($attributeCode);

        return $attribute ? $attribute->getValue() : null;
    }

    private function reindexCustomerGridRow(int $customerId): void
    {
        try {
            $this->indexerRegistry
                ->get(CustomerModel::CUSTOMER_GRID_INDEXER_ID)
                ->reindexRow($customerId);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Unable to reindex customer loyalty grid row.',
                ['customer_id' => $customerId, 'exception' => $exception]
            );
        }
    }
}
