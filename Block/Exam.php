<?php

namespace Ict\LoyaltyTier\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Ict\LoyaltyTier\Model\ResourceModel\Exam\CollectionFactory as ExamCollectionFactory;
use Ict\LoyaltyTier\Model\LoyaltyManager;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class Exam extends Template
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var ExamCollectionFactory
     */
    private $examCollectionFactory;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var LoyaltyManager
     */
    private $loyaltyManager;

    /**
     * @var PriceCurrencyInterface|null
     */
    private $priceCurrency;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param ExamCollectionFactory $examCollectionFactory
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param LoyaltyManager $loyaltyManager
     * @param array $data
     * @param PriceCurrencyInterface|null $priceCurrency
     */
    public function __construct(
        Context $context,
        // phpcs:ignore MEQP2.Classes.MutableObjects.MutableObjects
        CustomerSession $customerSession,
        ExamCollectionFactory $examCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        LoyaltyManager $loyaltyManager,
        array $data = [],
        ?PriceCurrencyInterface $priceCurrency = null
    ) {
        $this->customerSession = $customerSession;
        $this->examCollectionFactory = $examCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->loyaltyManager = $loyaltyManager;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    /**
     * Get current customer tier data.
     *
     * @return \Ict\LoyaltyTier\Model\ResourceModel\Exam\Collection|false
     */
    public function getTireData()
    {
        $spend = $this->getCustomerLifetimeSpend();
        $customerId = $this->customerSession->getCustomerId();

        if (!$customerId) {
            return false;
        }

        $collection = $this->examCollectionFactory->create();
        $collection->addFieldToFilter('status', 1);
        $collection->addFieldToFilter('minimum_spend', ['lteq' => $spend]);
        $collection->setOrder('minimum_spend', 'DESC');
        $collection->setPageSize(1);

        return $collection;
    }

    /**
     * Get next loyalty tier.
     *
     * @return \Ict\LoyaltyTier\Model\Exam|false
     */
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
        $collection->load();
        $items = $collection->getItems();
        $item = reset($items);

        return $item ?: $collection->getNewEmptyItem();
    }

    /**
     * Get customer lifetime spend.
     *
     * @return float|int
     */
    public function getCustomerLifetimeSpend()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return 0;
        }

        return $this->loyaltyManager->getCustomerLifetimeSpend((int) $this->customerSession->getCustomerId());
    }

    /**
     * Get remaining usage limit for tier.
     *
     * @param \Ict\LoyaltyTier\Model\Exam $tier
     * @return \Magento\Framework\Phrase|int
     */
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

    /**
     * Format currency amount.
     *
     * @param float|int|string $value
     * @return string
     */
    public function formatCurrency($value): string
    {
        return $this->getPriceCurrency()->format(
            (float) $value,
            false,
            PriceCurrencyInterface::DEFAULT_PRECISION
        );
    }

    /**
     * Format percent value.
     *
     * @param float|int|string $value
     * @return string
     */
    public function formatPercent($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.') . '%';
    }

    /**
     * Get price currency service.
     *
     * @return PriceCurrencyInterface
     */
    private function getPriceCurrency(): PriceCurrencyInterface
    {
        if (!$this->priceCurrency) {
            $this->priceCurrency = ObjectManager::getInstance()->get(PriceCurrencyInterface::class);
        }

        return $this->priceCurrency;
    }
}
