<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Ui\DataProvider\Supplier\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Cyper\PriceIntelligent\Model\ResourceModel\Supplier\CollectionFactory;
use Magento\Framework\App\RequestInterface;

class DataProvider extends AbstractDataProvider
{
    protected $loadedData;
    protected $request;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        foreach ($items as $supplier) {
            $this->loadedData[$supplier->getId()] = $supplier->getData();
        }

        // Return empty array for new records
        if (!$this->loadedData) {
            $this->loadedData = [];
        }

        return $this->loadedData;
    }
}
