<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Ui\DataProvider\Competitor\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Cyper\PriceIntelligent\Model\ResourceModel\Competitor\CollectionFactory;
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
        foreach ($items as $competitor) {
            $data = $competitor->getData();
            
            // Pretty print JSON fields for better readability
            if (isset($data['crawler_config']) && is_string($data['crawler_config'])) {
                $decoded = json_decode($data['crawler_config'], true);
                if ($decoded !== null) {
                    $data['crawler_config'] = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                }
            }
            
            $this->loadedData[$competitor->getId()] = $data;
        }

        // Return empty array for new records
        if (!$this->loadedData) {
            $this->loadedData = [];
        }

        return $this->loadedData;
    }
}
