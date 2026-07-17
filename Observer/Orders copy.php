<?php
namespace Vendor\Module\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class MyObserver implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @param CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        CollectionFactory $orderCollectionFactory
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $customerEmail = $observer->getEvent()->getCustomerEmail();

        $orders = $this->orderCollectionFactory->create()
            ->addAttributeToFilter('customer_email', $customerEmail);
        
        foreach ($orders as $order) {
            $grandTotal = $order->getGrandTotal();
        //    echo $grandTotal;die;
        }
    }
}
