<?php

namespace Ict\LoyaltyTier\Observer;

use Ict\LoyaltyTier\Model\ResourceModel\Tier;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Psr\Log\LoggerInterface;

class UpdateLoyaltyTier implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Tier
     */
    private $tierResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CollectionFactory $orderCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param Tier $tierResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory $orderCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        Tier $tierResource,
        LoggerInterface $logger
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->customerRepository = $customerRepository;
        $this->tierResource = $tierResource;
        $this->logger = $logger;
    }

    /**
     * Log loyalty tier event trigger.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->logger->info('Loyalty Tier Event Triggered');
    }
}
