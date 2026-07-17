<?php
namespace Ict\LoyaltyTier\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Psr\Log\LoggerInterface;

class UpdateLoyaltyTier implements ObserverInterface
{
    protected $orderCollectionFactory;
    protected $customerRepository;
    protected $ExamTierResource;
      protected $logger;
    // public function __construct(
    //     CollectionFactory $orderCollectionFactory,
    //     CustomerRepositoryInterface $customerRepository,
    //     \Ict\LoyaltyTier\Model\ResourceModel\Exam $ExamTierResource
    // ) {
    //     $this->orderCollectionFactory = $orderCollectionFactory;
    //     $this->customerRepository = $customerRepository;
    //     $this->ExamTierResource = $ExamTierResource;
    // }

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Ict\LoyaltyTier\Model\ResourceModel\Exam $examTierResource,
        LoggerInterface $logger // Use PSR Logger
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->customerRepository = $customerRepository;
        $this->examTierResource = $examTierResource;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        // This will log to var/log/system.log
        $this->logger->info("Loyalty Tier Event Triggered");
    }

    // public function execute(\Magento\Framework\Event\Observer $observer)
    // {
    //     $order = $observer->getEvent()->getOrder();
    //     $customerId = $order->getCustomerId();
    //     if (!$customerId) return;

    //     $lifetimeSpend = $this->getCustomerLifetimeSpend($customerId);

    //     $newTier = $this->ExamTierResource->getHighestEligibleTier($lifetimeSpend);
    //     $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/observer.log');
    //     $logger = new \Zend_Log();
    //     $logger->addWriter($writer);
    //     $logger->info('Observer ',$lifetimeSpend);

    //     if ($newTier) {
    //         $customer = $this->customerRepository->getById($customerId);
    //         $customer->setCustomAttribute('loyalty_tier', $newTier->getTierId());
    //         $this->customerRepository->save($customer);
    //     }
    // }

    // protected function getCustomerLifetimeSpend($customerId)
    // {
    //     $orders = $this->orderCollectionFactory->create()
    //         ->addFieldToFilter('customer_id', $customerId)
    //         ->addFieldToFilter('status', ['in' => ['complete', 'processing']]);
        
    //     $lifetimeSpend = 0;
    //     foreach ($orders as $order) {
    //         $lifetimeSpend += $order->getBaseGrandTotal();
    //     }
    //     return $lifetimeSpend;
    // }
}
