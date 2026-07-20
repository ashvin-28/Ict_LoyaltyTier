<?php

namespace Ict\LoyaltyTier\Cron;

use Ict\LoyaltyTier\Model\Config;
use Ict\LoyaltyTier\Model\Email\Sender;
use Ict\LoyaltyTier\Model\LoyaltyManager;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Psr\Log\LoggerInterface;

class SendEncouragementEmails
{
    /**
     * @var CustomerCollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var LoyaltyManager
     */
    private $loyaltyManager;

    /**
     * @param Config $config
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoyaltyManager $loyaltyManager
     * @param Sender $emailSender
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Config $config,
        CustomerCollectionFactory $customerCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        LoyaltyManager $loyaltyManager,
        Sender $emailSender,
        LoggerInterface $logger
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->loyaltyManager = $loyaltyManager;
    }

    /**
     * Send scheduled tier encouragement emails.
     *
     * @return void
     */
    public function execute(): void
    {
        $collection = $this->customerCollectionFactory->create();
        $collection->addAttributeToSelect('email');

        foreach ($collection as $customerModel) {
            $this->loyaltyManager->sendEncouragementEmailIfEligible((int) $customerModel->getId());
        }
    }
}
