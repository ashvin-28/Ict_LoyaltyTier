<?php
namespace Ict\LoyaltyTier\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

   

    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
    

        $resultPage = $this->resultPageFactory->create();

        if ($id) {
            $Exam = $this->_objectManager->create(\Ict\LoyaltyTier\Model\Exam::class)->load($id);

            if ($Exam->getId()) {
                $name = $Exam->getName();

                $resultPage->getConfig()->getTitle()->prepend(__('%1', $name));
            } else {
                $resultPage->getConfig()->getTitle()->prepend(__('Exam Not Found'));
            }
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Exam'));
        }

        return $resultPage;
    }
}
