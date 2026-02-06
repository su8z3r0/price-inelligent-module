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
            $data = $supplier->getData();
            
            // Pretty print JSON fields for better readability
            if (isset($data['source_config']) && is_string($data['source_config'])) {
                $decoded = json_decode($data['source_config'], true);
                if ($decoded !== null) {
                    $data['source_config'] = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                }
            }
            
            $this->loadedData[$supplier->getId()] = $data;
        }

        // Return empty array for new records
        if (!$this->loadedData) {
            $this->loadedData = [];
        }

        return $this->loadedData;
    }
}
