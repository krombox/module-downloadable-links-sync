<?php

namespace Krombox\DownloadableLinksSync\Block\Adminhtml\Product\Edit\Button;

use Krombox\DownloadableLinksSync\Model\Config;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Store\Model\StoreManagerInterface;

class SyncLinks extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic
{
    public function __construct(
        Context $context,
        Registry $registry,
        private Config $config,
        private StoreManagerInterface $storeManager
    ) {
        parent::__construct($context, $registry);
    }

    /**
     * @return mixed[]
     */
    public function getButtonData(): array
    {
        $data = [];

        if ($this->config->isEnabled()) {
            $product = $this->getProduct();
            if ($product->getId() && $product->getTypeId() === Type::TYPE_DOWNLOADABLE) {
                $data = [
                    'label' => __('Sync Links'),
                    'on_click' => '',
                    'id' => 'product-edit-sync-links-button',
                    'class' => 'action-secondary',
                    'data_attribute' => [
                        'url' => $this->getSyncLinksUrl()
                    ],
                    'sort_order' => 10
                ];
            }
        }

        return $data;
    }

    private function getSyncLinksUrl(): string
    {
        return $this->getUrl(
            'krombox_DownloadableLinksSync/link/sync',
            ['product_id' => $this->getProduct()->getId(), 'store' => $this->storeManager->getStore()->getId()]
        );
    }
}
