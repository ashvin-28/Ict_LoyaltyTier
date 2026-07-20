<?php

namespace Ict\LoyaltyTier\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;

class RecollectActiveQuotesAfterConfigChange implements ObserverInterface
{
    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @param QuoteResource $quoteResource
     */
    public function __construct(
        QuoteResource $quoteResource
    ) {
        $this->quoteResource = $quoteResource;
    }

    /**
     * Mark active quotes for totals recollection after loyalty configuration changes.
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer): void
    {
        $connection = $this->quoteResource->getConnection();
        $connection->update(
            $this->quoteResource->getMainTable(),
            ['trigger_recollect' => 1],
            [
                'is_active = ?' => 1,
                'items_count > ?' => 0,
            ]
        );
    }
}
