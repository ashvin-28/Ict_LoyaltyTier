<?php

namespace Ict\LoyaltyTier\Model;

use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class TierImage
{
    public const BASE_PATH = 'loyaltytier/tier';

    private const EMPTY_IMAGE_VALUE = 'image';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var File
     */
    private $fileIo;

    /**
     * @param StoreManagerInterface $storeManager
     * @param File $fileIo
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        File $fileIo
    ) {
        $this->storeManager = $storeManager;
        $this->fileIo = $fileIo;
    }

    /**
     * Normalize image value for database storage.
     *
     * @param string|null $image
     * @return string
     */
    public function normalize(?string $image): string
    {
        $image = ltrim(trim((string) $image), '/');

        if ($image === '' || $image === self::EMPTY_IMAGE_VALUE) {
            return '';
        }

        $mediaPosition = strpos($image, '/media/');
        if ($mediaPosition !== false) {
            return ltrim(substr($image, $mediaPosition + 7), '/');
        }

        if (strpos($image, '/') !== false) {
            return $image;
        }

        return self::BASE_PATH . '/' . $this->getFileName($image);
    }

    /**
     * Get media URL for a tier image.
     *
     * @param string|null $image
     * @return string
     */
    public function getUrl(?string $image): string
    {
        $image = $this->normalize($image);

        if ($image === '') {
            return '';
        }

        return $this->getMediaBaseUrl() . $image;
    }

    /**
     * Get file name from image path.
     *
     * @param string|null $image
     * @return string
     */
    public function getFileName(?string $image): string
    {
        $image = trim((string) $image);

        if ($image === '') {
            return '';
        }

        $pathInfo = $this->fileIo->getPathInfo($image);

        return (string) ($pathInfo['basename'] ?? '');
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
