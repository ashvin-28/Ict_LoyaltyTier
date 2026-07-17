<?php

namespace Ict\LoyaltyTier\Controller\Adminhtml\Index;

use Ict\LoyaltyTier\Model\ExamFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Delete extends Action
{
    /**
     * @var ExamFactory
     */
    private $examFactory;

    /**
     * @param Context $context
     * @param ExamFactory $examFactory
     */
    public function __construct(
        Context $context,
        ExamFactory $examFactory
    ) {
        $this->examFactory = $examFactory;
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
            $exam = $this->examFactory->create();
            $exam->load($id);
            $exam->delete();
            $this->messageManager->addSuccessMessage(__('You deleted the Tier.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('*/*/');
    }
}
