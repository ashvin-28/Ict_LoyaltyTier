<?php
namespace Ict\LoyaltyTier\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;

class CalculateLifetimeSpend implements ObserverInterface
{

    protected $logger;
    protected $customerRepository;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->logger=$logger;
    }

    public function execute(Observer $observer)
    {
        
        // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/observer.log');
        // $logger = new \Zend_Log();
        // $logger->addWriter($writer);
        // $logger->info('Observer Triggered');
        //  $this->logger->info('Observer Triggered');
        // $order = $observer->getEvent()->getOrder();

        // $customerId = $order->getCustomerId();
         $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();

        // if ($customerId) {
        //     // Get customer data including lifetime spend
        //     $customer = $this->customerRepository->getById($customerId);

        //     // Retrieve lifetime sales from custom attribute or extension attribute
        //     // Magento 2 stores this in customer_sales_order_grid
        //     $lifetimeSpend = $customer->getCustomAttribute('lifetime')
        //         ? $customer->getCustomAttribute('lifetime')->getValue()
        //         : 0;

        //     // YOUR LOGIC HERE (e.g., $lifetimeSpend)
        // }
    }
}
