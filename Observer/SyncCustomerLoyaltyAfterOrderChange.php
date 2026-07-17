<?php

namespace Ict\LoyaltyTier\Observer;

use Ict\LoyaltyTier\Model\LoyaltyManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class SyncCustomerLoyaltyAfterOrderChange implements ObserverInterface
{
    /**
     * @var LoyaltyManager
     */
    private $loyaltyManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        LoyaltyManager $loyaltyManager,
        LoggerInterface $logger
    ) {
        $this->loyaltyManager = $loyaltyManager;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $order = $this->getOrderFromObserver($observer);
        if (!$order || !$order->getCustomerId()) {
            return;
        }

        try {
            $this->loyaltyManager->syncCustomerTier((int) $order->getCustomerId());
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Unable to sync customer loyalty after order change.',
                ['order_id' => $order->getEntityId(), 'exception' => $exception]
            );
        }
    }

    private function getOrderFromObserver(Observer $observer): ?Order
    {
        $event = $observer->getEvent();
        $order = $event->getOrder() ?: $event->getDataObject() ?: $event->getObject();

        return $order instanceof Order ? $order : null;
    }
}
