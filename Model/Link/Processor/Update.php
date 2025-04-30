<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Processor;

use Krombox\DownloadableLinksSync\Api\MessageInterface;
use Krombox\DownloadableLinksSync\Model\Link\Manager;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Downloadable\Model\Link\Purchased\Item;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Store\Api\StoreRepositoryInterface;

class Update implements ProcessorInterface
{
    /**
     * Update constructor.
     *
     * @param Iterator $iterator
     * @param Copy $objectCopyService
     * @param Manager $linkManager
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        private readonly Iterator $iterator,
        private readonly Copy $objectCopyService,
        private readonly Manager $linkManager,
        private readonly StoreRepositoryInterface $storeRepository
    ) {
    }

    /**
     * Method process
     *
     * @param MessageInterface $message
     *
     * @return void
     */
    public function process(MessageInterface $message): void
    {
        foreach ($this->storeRepository->getList() as $store) {
            $storeId = $store->getId();
            $linkToUpdate = $this->linkManager->getLink($message->getLinkId(), $storeId);

            /** If link removed stop further processing */
            if (!$linkToUpdate) {
                return;
            }

            $linkPurchasedCollection = $this->linkManager->getLinkPurchasedItemCollectionByIds(
                $message->getIds(),
                $storeId
            );
            $this->iterator->walk(
                $linkPurchasedCollection->getSelect(),
                [[$this, 'updateLink']],
                ['link' => $linkToUpdate]
            );
        }
    }

    /**
     * @param mixed[] $args
     *
     * @return void
     */
    public function updateLink(array $args): void
    {
        $linkPurchasedItem = $args['row'];
        $link = $args['link'];

        $linkPurchasedItem = $this->linkManager->createLinkPurchasedItemModel()->addData($linkPurchasedItem);
        $this->objectCopyService->copyFieldsetToTarget(
            'downloadable_sales_copy_link',
            'to_purchased',
            $link,
            $linkPurchasedItem
        );

        $numberOfDownloads = $this->getNumberOfDownloads($link, $linkPurchasedItem);
        $linkPurchasedItem->setNumberOfDownloadsBought($numberOfDownloads);
        $this->linkManager->saveLinkPurchasedItem($linkPurchasedItem);
    }

    private function getNumberOfDownloads(LinkInterface $link, Item $item): int
    {
        return $link->getNumberOfDownloads() * $item['order_item_qty_ordered'];
    }
}
