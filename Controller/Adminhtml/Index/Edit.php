<?php

namespace Ict\LoyaltyTier\Controller\Adminhtml\Index;

use Ict\LoyaltyTier\Model\TierFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var TierFactory
     */
    private $tierFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param TierFactory $tierFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        TierFactory $tierFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->tierFactory = $tierFactory;
    }

    /**
     * Edit loyalty tier page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');

        $resultPage = $this->resultPageFactory->create();

        if ($id) {
            $tier = $this->tierFactory->create()->load($id);

            if ($tier->getId()) {
                $name = $tier->getName();

                $resultPage->getConfig()->getTitle()->prepend(__('%1', $name));
            } else {
                $resultPage->getConfig()->getTitle()->prepend(__('Tier Not Found'));
            }
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Tier'));
        }

        return $resultPage;
    }
}
