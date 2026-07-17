<?php

namespace Ict\LoyaltyTier\Ui\Component\Listing\Column;

use Ict\LoyaltyTier\Model\TierImage as TierImageModel;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class TierImage extends Column
{
    /**
     * @var TierImageModel
     */
    private $tierImage;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param TierImageModel $tierImage
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TierImageModel $tierImage,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->tierImage = $tierImage;
    }

    /**
     * Prepare image thumbnail data.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            $url = $this->tierImage->getUrl($item[$fieldName] ?? '');
            $item[$fieldName . '_src'] = $url;
            $item[$fieldName . '_orig_src'] = $url;
            $item[$fieldName . '_alt'] = $item['name'] ?? '';
        }

        return $dataSource;
    }
}
