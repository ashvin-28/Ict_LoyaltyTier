<?php
namespace Ict\LoyaltyTier\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Delete extends Action
{
    public $ExamFactory;
    
    public function __construct(
        Context $context,
        \Ict\LoyaltyTier\Model\ExamFactory $ExamFactory
    ) {
        $this->ExamFactory = $ExamFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('entity_id');
        try {
            $Exam = $this->ExamFactory->create();
            $Exam->load($id);
            $Exam->delete();
            $this->messageManager->addSuccessMessage(__('You deleted the Tier.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('*/*/');
    }
}
