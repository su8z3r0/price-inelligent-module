<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Ui\DataProvider;

use Cyper\PriceIntelligent\Model\ResourceModel\Competitor\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class CompetitorDataProvider extends AbstractDataProvider
{
    private array $loadedData = [];

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        private readonly DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        foreach ($items as $competitor) {
            $data = $competitor->getData();
            
            // Decodifica crawler_config da JSON per l'editor
            if (isset($data['crawler_config']) && is_string($data['crawler_config'])) {
                $decoded = json_decode($data['crawler_config'], true);
                if ($decoded !== null) {
                    $data['crawler_config'] = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                }
            }
            
            $this->loadedData[$competitor->getId()] = $data;
        }

        // Check if there is data in session (for validation errors)
        $data = $this->dataPersistor->get('cyper_competitor');
        if (!empty($data)) {
            $competitor = $this->collection->getNewEmptyItem();
            $competitor->setData($data);
            $this->loadedData[$competitor->getId()] = $competitor->getData();
            $this->dataPersistor->clear('cyper_competitor');
        }

        return $this->loadedData;
    }
}
