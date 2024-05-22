<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Processor;

use Krombox\DownloadableLinksSync\Api\MessageInterface;
use Krombox\DownloadableLinksSync\Model\Link\Manager;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Downloadable\Model\Link\Purchased\Item;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Sales\Api\Data\OrderItemInterface;

class Update implements ProcessorInterface
{
    public const ACTION_NAME = 'update';

    public function __construct(
        private Iterator $iterator,
        private Copy $objectCopyService,
        private Manager $linkManager,
        private ResourceConnection $connection
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
        $linkToUpdate = $this->linkManager->getLink($message->getLinkId());

        /** If link removed stop further processing */
        if (!$linkToUpdate) {
            return;
        }

        $linkPurchasedCollection = $this->getLinkPurchasedItemCollectionByIds($message->getIds());
        $this->iterator->walk(
            $linkPurchasedCollection->getSelect(),
            [[$this, 'updateLink']],
            ['link' => $linkToUpdate]
        );
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

    /**
     * @param string[] $ids
     *
     * @return \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection
     */
    private function getLinkPurchasedItemCollectionByIds(array $ids)
    {
        $connection = $this->connection->getConnection();
        $orderItemTableName = $connection->getTableName('sales_order_item');

        $orderItemJoinCondition = [
            $orderItemTableName . '.' . OrderItemInterface::ITEM_ID . ' = main_table.order_item_id'
        ];

        return $this->linkManager->createLinkPurchasedItemCollection()
            ->addFieldToFilter('main_table.item_id', ['in' => $ids])
            ->join(
                $orderItemTableName,
                implode(' AND ', $orderItemJoinCondition),
                ['order_item_qty_ordered' => OrderItemInterface::QTY_ORDERED]
            );
    }
}
