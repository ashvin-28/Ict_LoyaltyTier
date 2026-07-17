<?php

namespace Ict\LoyaltyTier\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Ict\LoyaltyTier\Model\ExamFactory;

class Save extends Action
{
    /**
     * @var ExamFactory
     */
    private $examFactory;

    /**
     * @param Action\Context $context
     * @param ExamFactory $examFactory
     */
    public function __construct(
        Action\Context $context,
        ExamFactory $examFactory
    ) {
        parent::__construct($context);
        $this->examFactory = $examFactory;
    }

    /**
     * Save loyalty tier.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            if (empty($data['entity_id'])) {
                unset($data['entity_id']);
            }

            $model = $this->examFactory->create();

            if (!empty($data['entity_id'])) {
                $model->load($data['entity_id']);
            }

            try {
                $model->setData($data);
                $model->save();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $this->_redirect('*/*/');
            }

            $this->messageManager->addSuccessMessage(__('Saved Successfully'));
        }

        return $this->_redirect('*/*/');
    }
}
