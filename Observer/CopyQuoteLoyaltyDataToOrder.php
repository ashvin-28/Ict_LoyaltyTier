<?php

namespace Ict\LoyaltyTier\Observer;

use Ict\LoyaltyTier\Model\Config;
use Ict\LoyaltyTier\Model\LoyaltyManager;
use Ict\LoyaltyTier\Model\Tier;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class CopyQuoteLoyaltyDataToOrder implements ObserverInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var LoyaltyManager
     */
    private $loyaltyManager;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Config|null
     */
    private $config;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoyaltyManager $loyaltyManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param Config|null $config
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        LoyaltyManager $loyaltyManager,
        PriceCurrencyInterface $priceCurrency,
        ?Config $config = null
    ) {
        $this->customerRepository = $customerRepository;
        $this->loyaltyManager = $loyaltyManager;
        $this->priceCurrency = $priceCurrency;
        $this->config = $config;
    }

    /**
     * Copy loyalty data from quote to order.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Quote|null $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var Order|null $order */
        $order = $observer->getEvent()->getOrder();

        if (!$quote || !$order) {
            return;
        }

        if (!$this->getConfig()->isEnabled($quote->getStoreId())) {
            $snapshot = $this->getEmptySnapshot();
            $this->applySnapshot($quote, $order, $snapshot);
            return;
        }

        $snapshot = $this->getQuoteLoyaltySnapshot($quote);
        if ($snapshot['base_loyalty_discount_amount'] <= 0.0001 || !$snapshot['loyalty_tier_id']) {
            $snapshot = $this->buildLoyaltySnapshot($quote);
        }

        $this->applySnapshot($quote, $order, $snapshot);
    }

    /**
     * Get quote loyalty snapshot.
     *
     * @param Quote $quote
     * @return array
     */
    private function getQuoteLoyaltySnapshot(Quote $quote): array
    {
        return [
            'loyalty_tier_id' => $quote->getData('loyalty_tier_id') ?: null,
            'loyalty_tier_name' => $quote->getData('loyalty_tier_name') ?: null,
            'loyalty_discount_amount' => (float) $quote->getData('loyalty_discount_amount'),
            'base_loyalty_discount_amount' => (float) $quote->getData('base_loyalty_discount_amount'),
        ];
    }

    /**
     * Build loyalty snapshot.
     *
     * @param Quote $quote
     * @return array
     */
    private function buildLoyaltySnapshot(Quote $quote): array
    {
        $emptySnapshot = $this->getEmptySnapshot();

        $customerId = (int) $quote->getCustomerId();
        if ($customerId <= 0) {
            return $emptySnapshot;
        }

        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $exception) {
            return $emptySnapshot;
        }

        $tier = $this->loyaltyManager->getCustomerDiscountTier($customer);
        if (!$tier || !$this->loyaltyManager->canUseTierDiscount($customer, $tier)) {
            return $emptySnapshot;
        }

        $baseDiscount = $this->calculateBaseDiscount($quote, $tier);
        if ($baseDiscount <= 0.0001) {
            return $emptySnapshot;
        }

        return [
            'loyalty_tier_id' => (int) $tier->getId(),
            'loyalty_tier_name' => (string) $tier->getName(),
            'loyalty_discount_amount' => (float) $this->priceCurrency->convertAndRound(
                $baseDiscount,
                $quote->getStore()
            ),
            'base_loyalty_discount_amount' => $baseDiscount,
        ];
    }

    /**
     * Calculate base discount.
     *
     * @param Quote $quote
     * @param Tier $tier
     * @return float
     */
    private function calculateBaseDiscount(Quote $quote, Tier $tier): float
    {
        $baseDiscountableAmount = 0.0;
        foreach ($quote->getAllVisibleItems() as $item) {
            $baseDiscountableAmount += (float) $item->getBaseRowTotal();
        }

        $discountPercent = max(0.0, (float) $tier->getDiscount());
        $baseDiscount = $this->priceCurrency->round($baseDiscountableAmount * ($discountPercent / 100));

        return min($baseDiscount, $baseDiscountableAmount);
    }

    /**
     * Get empty loyalty snapshot.
     *
     * @return array
     */
    private function getEmptySnapshot(): array
    {
        return [
            'loyalty_tier_id' => null,
            'loyalty_tier_name' => null,
            'loyalty_discount_amount' => 0.0,
            'base_loyalty_discount_amount' => 0.0,
        ];
    }

    /**
     * Apply loyalty snapshot to quote and order.
     *
     * @param Quote $quote
     * @param Order $order
     * @param array $snapshot
     * @return void
     */
    private function applySnapshot(Quote $quote, Order $order, array $snapshot): void
    {
        $quote->setData('loyalty_tier_id', $snapshot['loyalty_tier_id']);
        $quote->setData('loyalty_tier_name', $snapshot['loyalty_tier_name']);
        $quote->setData('loyalty_discount_amount', $snapshot['loyalty_discount_amount']);
        $quote->setData('base_loyalty_discount_amount', $snapshot['base_loyalty_discount_amount']);

        $order->setData('loyalty_tier_id', $snapshot['loyalty_tier_id']);
        $order->setData('loyalty_tier_name', $snapshot['loyalty_tier_name']);
        $order->setData('loyalty_discount_amount', $snapshot['loyalty_discount_amount']);
        $order->setData('base_loyalty_discount_amount', $snapshot['base_loyalty_discount_amount']);
    }

    /**
     * Get loyalty configuration.
     *
     * @return Config
     */
    private function getConfig(): Config
    {
        if (!$this->config) {
            $this->config = ObjectManager::getInstance()->get(Config::class);
        }

        return $this->config;
    }
}
