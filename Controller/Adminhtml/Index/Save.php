<?php

namespace Ict\LoyaltyTier\Controller\Adminhtml\Index;

use Ict\LoyaltyTier\Model\TierFactory;
use Ict\LoyaltyTier\Model\TierImage;
use Magento\Backend\App\Action;

class Save extends Action
{
    /**
     * @var TierFactory
     */
    private $tierFactory;

    /**
     * @var TierImage
     */
    private $tierImage;

    /**
     * @param Action\Context $context
     * @param TierFactory $tierFactory
     * @param TierImage $tierImage
     */
    public function __construct(
        Action\Context $context,
        TierFactory $tierFactory,
        TierImage $tierImage
    ) {
        parent::__construct($context);
        $this->tierFactory = $tierFactory;
        $this->tierImage = $tierImage;
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

            if (array_key_exists('image', $data)) {
                $data['image'] = $this->getImagePath($data['image']);
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

    /**
     * Get image path from image uploader data.
     *
     * @param array|string|null $imageData
     * @return string
     */
    private function getImagePath($imageData): string
    {
        if (!is_array($imageData)) {
            return $this->tierImage->normalize((string) $imageData);
        }

        $image = reset($imageData);
        if (!is_array($image)) {
            return '';
        }

        return $this->tierImage->normalize($image['file'] ?? $image['name'] ?? '');
    }
}
