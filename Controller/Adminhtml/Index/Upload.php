<?php

namespace Ict\LoyaltyTier\Controller\Adminhtml\Index;

use Ict\LoyaltyTier\Model\TierImage;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;

class Upload extends Action implements HttpPostActionInterface
{
    private const IMAGE_FIELD = 'image';

    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var AdapterFactory
     */
    private $adapterFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Action\Context $context
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param AdapterFactory $adapterFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Action\Context $context,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        AdapterFactory $adapterFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->adapterFactory = $adapterFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Upload tier image.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $result = $this->uploadImage();
        } catch (LocalizedException $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        } catch (\Throwable $e) {
            $result = [
                'error' => __('Something went wrong while uploading the image.'),
                'errorcode' => 0
            ];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    /**
     * Save uploaded image into media directory.
     *
     * @return array
     * @throws LocalizedException
     */
    private function uploadImage(): array
    {
        $imageId = $this->getRequest()->getParam('param_name', self::IMAGE_FIELD);
        $uploader = $this->uploaderFactory->create(['fileId' => $imageId]);
        $uploader->setAllowedExtensions(self::ALLOWED_EXTENSIONS);
        $uploader->addValidateCallback(
            'loyalty_tier_image',
            $this->adapterFactory->create(),
            'validateUploadFile'
        );
        $uploader->setAllowRenameFiles(true);
        $uploader->setAllowCreateFolders(true);
        $uploader->setFilesDispersion(false);

        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $result = $uploader->save($mediaDirectory->getAbsolutePath(TierImage::BASE_PATH));

        if (!is_array($result) || empty($result['file'])) {
            throw new LocalizedException(__('The image could not be uploaded.'));
        }

        $relativePath = TierImage::BASE_PATH . '/' . ltrim($result['file'], '/');
        unset($result['path'], $result['tmp_name']);

        $result['file'] = $relativePath;
        $result['url'] = $this->getMediaBaseUrl() . $relativePath;

        return $result;
    }

    /**
     * Get public media base URL.
     *
     * @return string
     */
    private function getMediaBaseUrl(): string
    {
        return rtrim(
            $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA),
            '/'
        ) . '/';
    }
}
