<?php

namespace Ict\LoyaltyTier\Observer;

use Ict\LoyaltyTier\Model\Exam;
use Ict\LoyaltyTier\Model\LoyaltyManager;
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

    public function __construct(
        ?CustomerRepositoryInterface $customerRepository = null,
        ?LoyaltyManager $loyaltyManager = null,
        ?PriceCurrencyInterface $priceCurrency = null
    ) {
        $objectManager = ObjectManager::getInstance();
        $this->customerRepository = $customerRepository
            ?: $objectManager->get(CustomerRepositoryInterface::class);
        $this->loyaltyManager = $loyaltyManager ?: $objectManager->get(LoyaltyManager::class);
        $this->priceCurrency = $priceCurrency ?: $objectManager->get(PriceCurrencyInterface::class);
    }

    public function execute(Observer $observer)
    {
        /** @var Quote|null $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var Order|null $order */
        $order = $observer->getEvent()->getOrder();

        if (!$quote || !$order) {
            return;
        }

        $snapshot = $this->getQuoteLoyaltySnapshot($quote);
        if ($snapshot['base_loyalty_discount_amount'] <= 0.0001 || !$snapshot['loyalty_tier_id']) {
            $snapshot = $this->buildLoyaltySnapshot($quote);
        }

        $quote->setData('loyalty_tier_id', $snapshot['loyalty_tier_id']);
        $quote->setData('loyalty_tier_name', $snapshot['loyalty_tier_name']);
        $quote->setData('loyalty_discount_amount', $snapshot['loyalty_discount_amount']);
        $quote->setData('base_loyalty_discount_amount', $snapshot['base_loyalty_discount_amount']);

        $order->setData('loyalty_tier_id', $snapshot['loyalty_tier_id']);
        $order->setData('loyalty_tier_name', $snapshot['loyalty_tier_name']);
        $order->setData('loyalty_discount_amount', $snapshot['loyalty_discount_amount']);
        $order->setData('base_loyalty_discount_amount', $snapshot['base_loyalty_discount_amount']);
    }

    private function getQuoteLoyaltySnapshot(Quote $quote): array
    {
        return [
            'loyalty_tier_id' => $quote->getData('loyalty_tier_id') ?: null,
            'loyalty_tier_name' => $quote->getData('loyalty_tier_name') ?: null,
            'loyalty_discount_amount' => (float) $quote->getData('loyalty_discount_amount'),
            'base_loyalty_discount_amount' => (float) $quote->getData('base_loyalty_discount_amount'),
        ];
    }

    private function buildLoyaltySnapshot(Quote $quote): array
    {
        $emptySnapshot = [
            'loyalty_tier_id' => null,
            'loyalty_tier_name' => null,
            'loyalty_discount_amount' => 0.0,
            'base_loyalty_discount_amount' => 0.0,
        ];

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

    private function calculateBaseDiscount(Quote $quote, Exam $tier): float
    {
        $baseDiscountableAmount = 0.0;
        foreach ($quote->getAllVisibleItems() as $item) {
            $baseDiscountableAmount += (float) $item->getBaseRowTotal();
        }

        $discountPercent = max(0.0, (float) $tier->getDiscount());
        $baseDiscount = $this->priceCurrency->round($baseDiscountableAmount * ($discountPercent / 100));

        return min($baseDiscount, $baseDiscountableAmount);
    }
}
