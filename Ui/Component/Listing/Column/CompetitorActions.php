<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class CompetitorActions extends Column
{
    private const URL_PATH_EDIT = 'price_intelligent/competitors/edit';
    private const URL_PATH_DELETE = 'price_intelligent/competitors/delete';

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        private UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        $name = $this->getData('name');

        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($item['competitor_id'])) {
                continue;
            }

            $item[$name]['edit'] = [
                'href' => $this->urlBuilder->getUrl(self::URL_PATH_EDIT, ['competitor_id' => $item['competitor_id']]),
                'label' => __('Edit'),
            ];

            $item[$name]['delete'] = [
                'href' => $this->urlBuilder->getUrl(self::URL_PATH_DELETE, ['competitor_id' => $item['competitor_id']]),
                'label' => __('Delete'),
                'confirm' => [
                    'title' => __('Delete Competitor'),
                    'message' => __('Are you sure you want to delete this competitor?')
                ]
            ];
        }

        return $dataSource;
    }
}
