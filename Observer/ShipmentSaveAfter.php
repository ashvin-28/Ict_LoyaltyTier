<?php

namespace Ict\LoyaltyTier\Observer;

use Ict\LoyaltyTier\Model\LoyaltyManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ShipmentSaveAfter implements ObserverInterface
{
    /**
     * @var LoyaltyManager
     */
    private $loyaltyManager;

    /**
     * @param LoyaltyManager $loyaltyManager
     */
    public function __construct(
        LoyaltyManager $loyaltyManager
    ) {
        $this->loyaltyManager = $loyaltyManager;
    }

    /**
     * Sync loyalty tier after shipment save.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
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
