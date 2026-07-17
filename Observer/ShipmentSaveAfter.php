<?php
namespace Ict\LoyaltyTier\Observer;

use Magento\Framework\Event\ObserverInterface;

class ShipmentSaveAfter implements ObserverInterface
{
    /**
     * @var \Ict\LoyaltyTier\Model\LoyaltyManager
     */
    private $loyaltyManager;

    public function __construct(
        \Ict\LoyaltyTier\Model\LoyaltyManager $loyaltyManager
    ) {
        $this->loyaltyManager = $loyaltyManager;
    }
    

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        $customerId = $order->getCustomerId();
        if (!$customerId) {
            return;
        }

        $this->loyaltyManager->syncCustomerTier((int) $customerId);
    }
}
