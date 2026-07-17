<?php

namespace Ict\LoyaltyTier\Controller\Adminhtml\Index;

use Ict\LoyaltyTier\Model\ExamFactory;
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
     * @var ExamFactory
     */
    private $examFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ExamFactory $examFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ExamFactory $examFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->examFactory = $examFactory;
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
            $exam = $this->examFactory->create()->load($id);

            if ($exam->getId()) {
                $name = $exam->getName();

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
