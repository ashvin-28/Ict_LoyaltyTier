<?php

namespace Ict\LoyaltyTier\Model\Exam;

use Ict\LoyaltyTier\Model\ResourceModel\Exam\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem\Io\File;
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
     * @var File
     */
    private $fileIo;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param RequestInterface $request
     * @param CollectionFactory $collectionFactory
     * @param File $fileIo
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        CollectionFactory $collectionFactory,
        File $fileIo,
        array $meta = [],
        array $data = []
    ) {
        $this->request = $request;
        $this->collectionFactory = $collectionFactory;
        $this->fileIo = $fileIo;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Return collection.
     *
     * @return \Ict\LoyaltyTier\Model\ResourceModel\Exam\Collection
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
                    $fileName = $data['image'];

                    $data['image'] = [
                        [
                            'name' => $this->fileIo->getPathInfo($fileName)['basename'],
                            'url'  => '/media/' . $fileName
                        ]
                    ];
                }

                $this->loadedData[$id] = $data;
            }
        } else {
            $this->loadedData = [];
        }

        return $this->loadedData;
    }
}
