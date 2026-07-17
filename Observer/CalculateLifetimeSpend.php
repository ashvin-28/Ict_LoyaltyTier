<?php
namespace Ict\LoyaltyTier\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

// $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/observer.log');
//         $logger = new \Zend_Log();
//         $logger->addWriter($writer);
//         $logger->info('Observer Triggered');
class CalculateLifetimeSpend implements ObserverInterface
{
    /** @var CustomerRepositoryInterface */
    protected $customerRepository;

    /** @var OrderCollectionFactory */
    protected $orderCollectionFactory;

    /** @var \Ict\LoyaltyTier\Model\ResourceModel\Exam */
    protected $loyaltyTierRepository;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param \Ict\LoyaltyTier\Model\ResourceModel\Exam $loyaltyTierRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        OrderCollectionFactory $orderCollectionFactory,
        \Ict\LoyaltyTier\Model\ResourceModel\Exam $loyaltyTierRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->loyaltyTierRepository = $loyaltyTierRepository;
    }

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
            ->addFieldToFilter('state', ['neq' => \Magento\Sales\Model\Order::STATE_CANCELED]);

        $totalSales = 0;
        foreach ($collection as $order) {
            $totalSales += $order->getGrandTotal();
        }

        return $totalSales;
    }
}
