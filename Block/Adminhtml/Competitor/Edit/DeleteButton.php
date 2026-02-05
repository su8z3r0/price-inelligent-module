<?php
declare(strict_types=1);

namespace Cyper\PriceIntelligent\Block\Adminhtml\Competitor\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton implements ButtonProviderInterface
{
    public function __construct(
        private readonly \Magento\Framework\App\RequestInterface $request
    ) {
    }

    public function getButtonData(): array
    {
        $competitorId = (int) $this->request->getParam('competitor_id');
        
        if (!$competitorId) {
            return [];
        }

        return [
            'label' => __('Delete'),
            'class' => 'delete',
            'on_click' => sprintf("deleteConfirm('%s', '%s')",
                __('Are you sure you want to delete this competitor?'),
                $this->getDeleteUrl()
            ),
            'sort_order' => 20
        ];
    }

    private function getDeleteUrl(): string
    {
        return sprintf(
            "location.href = '%s'",
            $this->getUrl('*/*/delete', ['competitor_id' => $this->request->getParam('competitor_id')])
        );
    }

    private function getUrl(string $route, array $params = []): string
    {
        return '';  // Will be replaced by real URL builder in context
    }
}
