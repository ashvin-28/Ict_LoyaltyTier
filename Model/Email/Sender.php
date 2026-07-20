<?php

namespace Ict\LoyaltyTier\Model\Email;

use Ict\LoyaltyTier\Model\Config;
use Ict\LoyaltyTier\Model\Tier;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Sender
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Config $config
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->logger = $logger;
    }

    /**
     * Send tier achievement email.
     *
     * @param CustomerInterface $customer
     * @param Tier $tier
     * @param float $lifetimeSpend
     * @return bool
     */
    public function sendAchievementEmail(
        CustomerInterface $customer,
        Tier $tier,
        float $lifetimeSpend
    ): bool {
        $storeId = $this->getCustomerStoreId($customer);
        if (!$this->config->isAchievementEmailEnabled($storeId)) {
            return false;
        }

        return $this->sendEmail(
            $customer,
            $this->config->getAchievementTemplate($storeId),
            $this->config->getAchievementSender($storeId),
            $storeId,
            [
                'tier' => $tier,
                'tier_name' => (string) $tier->getName(),
                'lifetime_spend' => $this->formatCurrency($lifetimeSpend, $storeId),
            ]
        );
    }

    /**
     * Send tier encouragement email.
     *
     * @param CustomerInterface $customer
     * @param Tier $nextTier
     * @param float $lifetimeSpend
     * @param float $spendRemaining
     * @return bool
     */
    public function sendEncouragementEmail(
        CustomerInterface $customer,
        Tier $nextTier,
        float $lifetimeSpend,
        float $spendRemaining
    ): bool {
        $storeId = $this->getCustomerStoreId($customer);
        if (!$this->config->isEncouragementEmailEnabled($storeId)) {
            return false;
        }

        return $this->sendEmail(
            $customer,
            $this->config->getEncouragementTemplate($storeId),
            $this->config->getEncouragementSender($storeId),
            $storeId,
            [
                'next_tier' => $nextTier,
                'next_tier_name' => (string) $nextTier->getName(),
                'next_tier_minimum_spend' => $this->formatCurrency((float) $nextTier->getMinimumSpend(), $storeId),
                'lifetime_spend' => $this->formatCurrency($lifetimeSpend, $storeId),
                'spend_remaining' => $this->formatCurrency($spendRemaining, $storeId),
            ]
        );
    }

    /**
     * Send configured email.
     *
     * @param CustomerInterface $customer
     * @param string $template
     * @param string $sender
     * @param int $storeId
     * @param array $variables
     * @return bool
     */
    private function sendEmail(
        CustomerInterface $customer,
        string $template,
        string $sender,
        int $storeId,
        array $variables
    ): bool {
        $email = trim((string) $customer->getEmail());
        if ($email === '') {
            return false;
        }

        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($template)
                ->setTemplateOptions([
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars($this->getTemplateVariables($customer, $variables))
                ->setFromByScope($sender, $storeId)
                ->addTo($email, $this->getCustomerName($customer))
                ->getTransport();
            $transport->sendMessage();
            return true;
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Unable to send loyalty tier email.',
                ['customer_id' => $customer->getId(), 'template' => $template, 'exception' => $exception]
            );
        }

        return false;
    }

    /**
     * Get common template variables.
     *
     * @param CustomerInterface $customer
     * @param array $variables
     * @return array
     */
    private function getTemplateVariables(CustomerInterface $customer, array $variables): array
    {
        return array_merge(
            [
                'customer' => $customer,
                'customer_name' => $this->getCustomerName($customer),
            ],
            $variables
        );
    }

    /**
     * Get customer display name.
     *
     * @param CustomerInterface $customer
     * @return string
     */
    private function getCustomerName(CustomerInterface $customer): string
    {
        $name = trim((string) $customer->getFirstname() . ' ' . (string) $customer->getLastname());

        return $name !== '' ? $name : (string) $customer->getEmail();
    }

    /**
     * Get customer store ID.
     *
     * @param CustomerInterface $customer
     * @return int
     */
    private function getCustomerStoreId(CustomerInterface $customer): int
    {
        $storeId = (int) $customer->getStoreId();
        if ($storeId > 0) {
            return $storeId;
        }

        return (int) $this->storeManager->getStore()->getId();
    }

    /**
     * Format amount using store currency.
     *
     * @param float $amount
     * @param int $storeId
     * @return string
     */
    private function formatCurrency(float $amount, int $storeId): string
    {
        return $this->priceCurrency->format($amount, false, PriceCurrencyInterface::DEFAULT_PRECISION, $storeId);
    }
}
