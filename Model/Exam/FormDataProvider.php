<?php
namespace Ict\LoyaltyTier\Model\Exam;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\RequestInterface;

class FormDataProvider extends AbstractDataProvider
{
    protected $collection;
    protected $loadedData;
    protected $request;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        \Ict\LoyaltyTier\Model\ResourceModel\Exam\CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->request = $request;
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $id = $this->request->getParam('entity_id');

        if ($id) {
            $item = $this->collection->getItemById($id);

            if ($item) {

                $data = $item->getData();

                if (!empty($data['image'])) {

                    $fileName = $data['image'];

                    $data['image'] = [
                    [
                        'name' => basename($fileName),
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
