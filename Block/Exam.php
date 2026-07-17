<?php

namespace Ict\LoyaltyTier\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Ict\LoyaltyTier\Model\ResourceModel\Exam\CollectionFactory as ExamCollectionFactory;
use Ict\LoyaltyTier\Model\LoyaltyManager;
// use Ict\LoyaltyTier\Model\Model\ExamFactory as LoyaltyCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class Exam extends Template
{
    protected $customerSession;
    protected $examCollectionFactory;
    protected $orderCollectionFactory;
    protected $loyaltyManager;
    protected $priceCurrency;
    // protected $loyaltyCollectionFactory;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        ExamCollectionFactory $examCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        LoyaltyManager $loyaltyManager,
        // LoyaltyCollectionFactory $loyaltyCollectionFactory,
        array $data = [],
        ?PriceCurrencyInterface $priceCurrency = null
    ) {
        $this->customerSession = $customerSession;
        $this->examCollectionFactory = $examCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->loyaltyManager = $loyaltyManager;
        $this->priceCurrency = $priceCurrency ?: ObjectManager::getInstance()->get(PriceCurrencyInterface::class);
        // $this->loyaltyCollectionFactory = $loyaltyCollectionFactory;
        parent::__construct($context, $data);
    }

    // public function getTireData()
    // {
    //     $customerId = $this->customerSession->getCustomerId();
    //     if (!$customerId) {
    //         return false;
    //     }
    //     return $this->examCollectionFactory->create()
    //         ->addFieldToFilter('entity_id', 4);
    // }
    public function getTireData()
    {
        $spend = $this->getCustomerLifetimeSpend();
        $customerId = $this->customerSession->getCustomerId();
        // echo $customerId;die;

        if (!$customerId) {
            return false;
        }


        // $loyaltyCollection = $this->loyaltyCollectionFactory->create();


        $collection = $this->examCollectionFactory->create();
        $collection->addFieldToFilter('status', 1);
        $collection->addFieldToFilter('minimum_spend', ['lteq' => $spend]);
        $collection->setOrder('minimum_spend', 'DESC');
        $collection->setPageSize(1);

        return $collection;
    }
    public function getNextTier()
    {
        $spend = $this->getCustomerLifetimeSpend();
        $customerId = $this->customerSession->getCustomerId();
        
        if (!$customerId || $spend === null) {
            return false;
        }

        $collection = $this->examCollectionFactory->create();
        $collection->addFieldToFilter('status', 1);
        $collection->addFieldToFilter('minimum_spend', ['gt' => $spend]);
        $collection->setOrder('minimum_spend', 'ASC');
        $collection->setPageSize(1);
        
        return $collection->getFirstItem();
    }


    public function getCustomerLifetimeSpend()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return 0;
        }

        return $this->loyaltyManager->getCustomerLifetimeSpend((int) $this->customerSession->getCustomerId());
    }

    public function getRemainingUsageLimit($tier)
    {
        $limit = (int) $tier->getData('limit');
        if ($limit <= 0) {
            return __('Unlimited');
        }

        $customerId = (int) $this->customerSession->getCustomerId();
        if ($customerId <= 0) {
            return $limit;
        }

        $remaining = $this->loyaltyManager->getRemainingUsageForCustomer($customerId, $tier);

        return $remaining === null ? __('Unlimited') : $remaining;
    }

    public function formatCurrency($value): string
    {
        return $this->priceCurrency->format(
            (float) $value,
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION
        );
    }

    public function formatPercent($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.') . '%';
    }
}
