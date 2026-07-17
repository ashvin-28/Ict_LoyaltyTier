<?php

namespace Ict\LoyaltyTier\Model\Tier;

use Ict\LoyaltyTier\Model\ResourceModel\Tier\CollectionFactory;
use Ict\LoyaltyTier\Model\TierImage;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class FormDataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array|null
     */
    private $loadedData;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var TierImage
     */
    private $tierImage;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param RequestInterface $request
     * @param CollectionFactory $collectionFactory
     * @param TierImage $tierImage
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        CollectionFactory $collectionFactory,
        TierImage $tierImage,
        array $meta = [],
        array $data = []
    ) {
        $this->request = $request;
        $this->collectionFactory = $collectionFactory;
        $this->tierImage = $tierImage;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Return collection.
     *
     * @return \Ict\LoyaltyTier\Model\ResourceModel\Tier\Collection
     */
    public function getCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->collectionFactory->create();
        }

        return $this->collection;
    }

    /**
     * Get form data.
     *
     * @return array|null
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $id = $this->request->getParam('entity_id');

        if ($id) {
            $item = $this->getCollection()->getItemById($id);

            if ($item) {
                $data = $item->getData();

                if (!empty($data['image'])) {
                    $fileName = $this->tierImage->normalize($data['image']);

                    if ($fileName) {
                        $data['image'] = [
                            [
                                'name' => $this->tierImage->getFileName($fileName),
                                'url' => $this->tierImage->getUrl($fileName),
                                'file' => $fileName
                            ]
                        ];
                    } else {
                        unset($data['image']);
                    }
                }

                $this->loadedData[$id] = $data;
            }
        } else {
            $this->loadedData = [];
        }

        return $this->loadedData;
    }
}
