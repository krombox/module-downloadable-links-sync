<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Queue;

use Krombox\DownloadableLinksSync\Model\Link\LinkOperationManager;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class QueueGenerator
{
    private const PAGE_SIZE = 100;

    /**
     * @param LinkOperationManager $linkOperationManager
     * @param CollectionFactory $productCollectionFactory
     * @param QueueService $queueService
     */
    public function __construct(
        private readonly LinkOperationManager $linkOperationManager,
        private readonly CollectionFactory $productCollectionFactory,
        private readonly QueueService $queueService
    ) {
    }

    /**
     * @param array $productIds
     *
     * @return void
     */
    public function generate(array $productIds): void
    {
        $this->queueService->clearQueue();
        $this->processProducts($productIds);
    }

    /**
     * @param array $productIds
     *
     * @return void
     */
    public function processProducts(array $productIds): void
    {
        $page = 1;

        do {
            $productCollection = $this->productCollectionFactory->create();
            $productCollection
                ->setPageSize(self::PAGE_SIZE)
                ->setCurPage($page);

            if ($productIds) {
                $productCollection->addFieldToFilter('entity_id', ['in' => $productIds]);
            }

            foreach ($productCollection as $product) {
                $this->linkOperationManager->syncProductLinks($product);
            }

            $itemsCount = $productCollection->count();
            $productCollection->clear();
            $page++;
        } while ($itemsCount === self::PAGE_SIZE);
    }
}
