<?php

namespace Ict\LoyaltyTier\Block;

// use Ict\LoyaltyTier\Model\ResourceModel\Exam\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Order;

class Exam extends \Magento\Framework\View\Element\Template
{
    protected $customerSession;
    protected $tireCollectionFactory;
    protected $collectionFactory;
    protected $orderCollectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Ict\LoyaltyTier\Model\ResourceModel\Exam\CollectionFactory $collectionFactory,
        CollectionFactory $orderCollectionFactory,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->collectionFactory = $collectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getTireData()
    {
        $customerId = $this->customerSession->getCustomerId();
        // echo $customerId;die;
        // Return collection filtered by $customerId
        // return $this->collectionFactory->create()->addFieldToFilter('entity_id', 4);
    }
    public function getCustomerLifetimeSpend()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return 0;
        }

        $customerId = $this->customerSession->getCustomerId();
        $orders = $this->orderCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('status', ['in' => [Order::STATE_COMPLETE, Order::STATE_PROCESSING]]);

        $lifetimeSpend = 0;
        foreach ($orders as $order) {
            $lifetimeSpend += $order->getBaseGrandTotal();
        }
        echo $lifetimeSpend;
        die;

        return $lifetimeSpend;
    }
    //     public function getTireData()
    // {
    //     $customer = $this->customerSession->getCustomer();
    //     $customerEmail = $customer->getEmail();

    //     $collection = $this->collectionFactory->create();


    //     $collection->getSelect()->where('customer_email_in_loyalty = ?', $customerEmail);

    //     return $collection;
    // }
}
