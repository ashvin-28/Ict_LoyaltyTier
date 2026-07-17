<?php

namespace Ict\LoyaltyTier\Observer;

use Ict\LoyaltyTier\Model\ResourceModel\Exam;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class MyObserver implements ObserverInterface
{
    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var OrderCollectionFactory */
    private $orderCollectionFactory;

    /** @var Exam */
    private $loyaltyTierRepository;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param Exam $loyaltyTierRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        OrderCollectionFactory $orderCollectionFactory,
        Exam $loyaltyTierRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->loyaltyTierRepository = $loyaltyTierRepository;
    }

    /**
     * Update customer tier from order event.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        $customerId = $order->getCustomerId();

        if (!$customerId) {
            return;
        }

        $lifetimeSpend = $this->calculateLifetimeSales($customerId);
        $tier = $this->loyaltyTierRepository->getHighestTier($lifetimeSpend);

        if ($tier) {
            $customer = $this->customerRepository->getById($customerId);
            
            $customer->setCustomAttribute('loyalty_tier', $tier->getTirename());
            
            $this->customerRepository->save($customer);
        }
    }

    /**
     * Calculate lifetime sales for a customer
     *
     * @param int $customerId
     * @return float
     */
    private function calculateLifetimeSales($customerId)
    {
        $collection = $this->orderCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('state', ['neq' => Order::STATE_CANCELED]);

        $totalSales = 0;
        foreach ($collection as $order) {
            $totalSales += $order->getGrandTotal();
        }

        return $totalSales;
    }
}
