<?php

namespace Ict\LoyaltyTier\Observer;

use Ict\LoyaltyTier\Model\ResourceModel\Exam;
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
     * @var Exam
     */
    private $examTierResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CollectionFactory $orderCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param Exam $examTierResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory $orderCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        Exam $examTierResource,
        LoggerInterface $logger
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->customerRepository = $customerRepository;
        $this->examTierResource = $examTierResource;
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
