<?php

namespace Ict\LoyaltyTier\Model\Total\Quote;

use Ict\LoyaltyTier\Model\LoyaltyManager;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

class Custom extends AbstractTotal
{
    private const TOTAL_CODE = 'customer_discount';

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var LoyaltyManager
     */
    private $loyaltyManager;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        CustomerRepositoryInterface $customerRepository,
        LoyaltyManager $loyaltyManager
    ) {
        $this->setCode(self::TOTAL_CODE);
        $this->priceCurrency = $priceCurrency;
        $this->customerRepository = $customerRepository;
        $this->loyaltyManager = $loyaltyManager;
    }

    public function _resetState(): void
    {
        parent::_resetState();
        $this->setCode(self::TOTAL_CODE);
    }

    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        if (!$shippingAssignment->getItems()) {
            return $this;
        }

        $this->resetQuoteLoyaltyData($quote);

        $customerId = (int) $quote->getCustomerId();
        if ($customerId <= 0) {
            return $this;
        }

        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $exception) {
            return $this;
        }

        $tier = $this->loyaltyManager->getCustomerDiscountTier($customer);
        if (!$tier || !$this->loyaltyManager->canUseTierDiscount($customer, $tier)) {
            return $this;
        }

        $baseDiscountableAmount = $this->getBaseDiscountableAmount($shippingAssignment, $total);
        $discountPercent = max(0.0, (float) $tier->getDiscount());
        $baseDiscount = min(
            $this->priceCurrency->round($baseDiscountableAmount * ($discountPercent / 100)),
            $baseDiscountableAmount
        );
        if ($baseDiscount <= 0.0001) {
            return $this;
        }

        $discount = (float) $this->priceCurrency->convertAndRound($baseDiscount, $quote->getStore());

        $total->addTotalAmount(self::TOTAL_CODE, -$discount);
        $total->addBaseTotalAmount(self::TOTAL_CODE, -$baseDiscount);

        $quote->setData('loyalty_tier_id', (int) $tier->getId());
        $quote->setData('loyalty_tier_name', (string) $tier->getName());
        $quote->setData('loyalty_discount_percent', $discountPercent);
        $quote->setData('loyalty_discount_amount', $discount);
        $quote->setData('base_loyalty_discount_amount', $baseDiscount);

        return $this;
    }

    public function fetch(Quote $quote, Total $total)
    {
        $amount = (float) $total->getTotalAmount(self::TOTAL_CODE);
        if (abs($amount) < 0.0001) {
            $amount = -abs((float) $quote->getData('loyalty_discount_amount'));
        }

        if (abs($amount) < 0.0001) {
            $amount = -abs($this->getPersistedAppliedLoyaltyDiscountAmount($quote));
        }

        if (abs($amount) < 0.0001) {
            return [];
        }

        return [
            'code' => self::TOTAL_CODE,
            'title' => $this->getTitle($quote),
            'value' => $amount,
        ];
    }

    public function getLabel()
    {
        return __('Tire Discount');
    }

    private function resetQuoteLoyaltyData(Quote $quote): void
    {
        $quote->setData('loyalty_tier_id', null);
        $quote->setData('loyalty_tier_name', null);
        $quote->setData('loyalty_discount_percent', 0);
        $quote->setData('loyalty_discount_amount', 0);
        $quote->setData('base_loyalty_discount_amount', 0);
    }

    private function getBaseDiscountableAmount(
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ): float {
        $baseItemsAmount = 0.0;
        foreach ($shippingAssignment->getItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            $baseItemsAmount += (float) $item->getBaseRowTotal();
        }

        if ($baseItemsAmount > 0.0001) {
            return $baseItemsAmount;
        }

        $baseAmounts = $total->getAllBaseTotalAmounts();
        unset($baseAmounts[self::TOTAL_CODE]);

        return max(0.0, (float) array_sum($baseAmounts));
    }

    private function getPersistedAppliedLoyaltyDiscountAmount(Quote $quote): float
    {
        if ((int) $quote->getItemsCount() <= 0 || (int) $quote->getCustomerId() <= 0) {
            return 0.0;
        }

        try {
            $customer = $this->customerRepository->getById((int) $quote->getCustomerId());
        } catch (NoSuchEntityException $exception) {
            return 0.0;
        }

        $tier = $this->loyaltyManager->getCustomerDiscountTier($customer);
        if (!$tier || !$this->loyaltyManager->canUseTierDiscount($customer, $tier)) {
            return 0.0;
        }

        $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        if (!$address || !$address->getId()) {
            return 0.0;
        }

        $discountableAmount = 0.0;
        foreach ($quote->getAllVisibleItems() as $item) {
            $discountableAmount += (float) $item->getRowTotal();
        }

        $expectedDiscount = $this->priceCurrency->round(
            $discountableAmount * (max(0.0, (float) $tier->getDiscount()) / 100)
        );
        if ($expectedDiscount <= 0.0001) {
            return 0.0;
        }

        $expectedGrandTotal = (float) $address->getSubtotal()
            + (float) $address->getShippingAmount()
            + (float) $address->getTaxAmount()
            + (float) $address->getDiscountAmount();
        $appliedDiscount = $this->priceCurrency->round(
            $expectedGrandTotal - (float) $address->getGrandTotal()
        );

        if (abs($appliedDiscount - $expectedDiscount) > 0.05) {
            return 0.0;
        }

        $quote->setData('loyalty_tier_id', (int) $tier->getId());
        $quote->setData('loyalty_tier_name', (string) $tier->getName());
        $quote->setData('loyalty_discount_percent', (float) $tier->getDiscount());
        $quote->setData('loyalty_discount_amount', $appliedDiscount);
        $quote->setData('base_loyalty_discount_amount', $appliedDiscount);

        return $appliedDiscount;
    }

    private function getTitle(Quote $quote): \Magento\Framework\Phrase
    {
        $tierName = trim((string) $quote->getData('loyalty_tier_name'));
        $discountPercent = $this->getDiscountPercent($quote);

        if ($tierName !== '' && $discountPercent !== null) {
            return __('Tire Discount (%1)(%2%)', $tierName, $this->formatPercent($discountPercent));
        }

        if ($tierName !== '') {
            return __('Tire Discount (%1)', $tierName);
        }

        return __('Tire Discount');
    }

    private function getDiscountPercent(Quote $quote): ?float
    {
        $discountPercent = (float) $quote->getData('loyalty_discount_percent');
        if ($discountPercent > 0.0001) {
            return $discountPercent;
        }

        $tierId = (int) $quote->getData('loyalty_tier_id');
        if ($tierId <= 0) {
            return null;
        }

        $tier = $this->loyaltyManager->getActiveTierById($tierId);
        if (!$tier || !$tier->getId()) {
            return null;
        }

        $discountPercent = (float) $tier->getDiscount();

        return $discountPercent > 0.0001 ? $discountPercent : null;
    }

    private function formatPercent(float $percent): string
    {
        return rtrim(rtrim(sprintf('%.2F', $percent), '0'), '.');
    }
}
