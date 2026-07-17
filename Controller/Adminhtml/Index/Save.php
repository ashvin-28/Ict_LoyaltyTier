<?php

namespace Ict\LoyaltyTier\Controller\Adminhtml\Index;

use Ict\LoyaltyTier\Model\TierFactory;
use Magento\Backend\App\Action;

class Save extends Action
{
    /**
     * @var TierFactory
     */
    private $tierFactory;

    /**
     * @param Action\Context $context
     * @param TierFactory $tierFactory
     */
    public function __construct(
        Action\Context $context,
        TierFactory $tierFactory
    ) {
        parent::__construct($context);
        $this->tierFactory = $tierFactory;
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

            $model = $this->tierFactory->create();

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
