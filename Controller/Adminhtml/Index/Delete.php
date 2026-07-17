<?php

namespace Ict\LoyaltyTier\Controller\Adminhtml\Index;

use Ict\LoyaltyTier\Model\TierFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Delete extends Action
{
    /**
     * @var TierFactory
     */
    private $tierFactory;

    /**
     * @param Context $context
     * @param TierFactory $tierFactory
     */
    public function __construct(
        Context $context,
        TierFactory $tierFactory
    ) {
        $this->tierFactory = $tierFactory;
        parent::__construct($context);
    }

    /**
     * Delete loyalty tier.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('entity_id');
        try {
            $tier = $this->tierFactory->create();
            $tier->load($id);
            $tier->delete();
            $this->messageManager->addSuccessMessage(__('You deleted the Tier.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('*/*/');
    }
}
