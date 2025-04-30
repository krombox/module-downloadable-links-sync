<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Processor;

use Krombox\DownloadableLinksSync\Api\MessageInterface;
use Krombox\DownloadableLinksSync\Model\Link\Manager;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Collection as LinkPurchasedCollection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class Add implements ProcessorInterface
{
    /**
     * @param Iterator $iterator
     * @param Copy $objectCopyService
     * @param ResourceConnection $connection
     * @param ScopeConfigInterface $scopeConfig
     * @param Manager $linkManager
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        private readonly Iterator $iterator,
        private readonly Copy $objectCopyService,
        private readonly ResourceConnection $connection,
        private readonly ScopeConfigInterface $scopeConfig,
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
            $linkToAdd = $this->linkManager->getLink($message->getLinkId(), $storeId);

            /** If link removed stop further processing */
            if (!$linkToAdd) {
                return;
            }

            $linkPurchasedCollection = $this->getLinkPurchasedCollectionWhereLinkMissedByIds(
                $message->getIds(),
                $linkToAdd,
                $storeId
            );
            $this->iterator->walk($linkPurchasedCollection->getSelect(), [[$this, 'addLink']], ['link' => $linkToAdd]);
        }
    }

    /**
     * Add Purchased Item with a link
     *
     * @param array<mixed> $args
     *
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function addLink(array $args): void
    {
        $item = $args['row'];
        $link = $args['link'];

        $linkPurchasedItem = $this->linkManager->createLinkPurchasedItemModel();
        $this->objectCopyService->copyFieldsetToTarget(
            'downloadable_sales_copy_link',
            'to_purchased',
            $link,
            $linkPurchasedItem
        );

        $linkStatus = $this->getLinkStatus($item);
        $linkHash = $this->generateLinkHash($item, $link);
        $numberOfDownloads = $this->getNumberOfDownloads($link, $item);

        $linkPurchasedItem
            ->setPurchasedId($item['purchased_id'])
            ->setOrderItemId($item['order_item_id'])
            //->setLinkTitle($link->getTitle())
            ->setLinkHash($linkHash)
            ->setNumberOfDownloadsBought($numberOfDownloads)
            ->setStatus($linkStatus);

        $this->linkManager->saveLinkPurchasedItem($linkPurchasedItem);
    }

    /**
     * @param array<mixed> $item
     *
     * @return string
     */
    private function getLinkStatus(array $item): string
    {
        $linkStatus = \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PENDING;
        $orderStatusToEnableItem = $this->getOrderItemStatusToEnable($item['order_store_id']);

        if ($orderStatusToEnableItem == \Magento\Sales\Model\Order\Item::STATUS_PENDING
            || $item['order_state'] == \Magento\Sales\Model\Order::STATE_COMPLETE
        ) {
            $linkStatus = \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_AVAILABLE;
        }

        return $linkStatus;
    }

    /**
     * @param LinkInterface $link
     * @param array<mixed> $item
     *
     * @return int
     */
    private function getNumberOfDownloads(LinkInterface $link, array $item): int
    {
        return $link->getNumberOfDownloads() * $item['order_item_qty_ordered'];
    }

    private function getOrderItemStatusToEnable(string $storeId): mixed
    {
        return $this->scopeConfig->getValue(
            \Magento\Downloadable\Model\Link\Purchased\Item::XML_PATH_ORDER_ITEM_STATUS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param mixed[] $linkPurchased
     * @param Link $link
     *
     * @return string
     */
    private function generateLinkHash(array $linkPurchased, Link $link): string
    {
        return strtr(base64_encode(microtime() . $linkPurchased['purchased_id'] .
            $linkPurchased['order_item_id']
            . $link->getProductId()), '+/=', '-_,');
    }

    /**
     * Get linkPurchased only when link_id is not already added. Could be the case when you click Sync button mupliple
     * times but queue still in progress
     *
     * @param string[] $ids
     *
     * @return LinkPurchasedCollection
     */
    private function getLinkPurchasedCollectionWhereLinkMissedByIds(
        array $ids,
        Link $link,
        int $storeId
    ): LinkPurchasedCollection {
        $connection = $this->connection->getConnection();
        $orderTableName = $connection->getTableName('sales_order');
        $orderItemTableName = $connection->getTableName('sales_order_item');

        $orderJoinCondition = [
            $orderTableName . '.' . OrderInterface::ENTITY_ID . ' = main_table.order_id'
        ];

        $orderItemJoinCondition = [
            $orderItemTableName . '.' . OrderItemInterface::ITEM_ID . ' = main_table.order_item_id',
            $orderItemTableName . '.' . OrderItemInterface::STORE_ID . ' = ' . $storeId
        ];

        $linkPurchasedCollection = $this->linkManager->getLinkPurchasedCollectionWhereLinkMissed($link)
            ->join(
                $orderTableName,
                implode(' AND ', $orderJoinCondition),
                [
                    'order_state' => OrderInterface::STATE,
                    'order_store_id' => OrderInterface::STORE_ID
                ]
            )
            ->join(
                $orderItemTableName,
                implode(' AND ', $orderItemJoinCondition),
                ['order_item_qty_ordered' => OrderItemInterface::QTY_ORDERED]
            )
            ->addFieldToSelect('purchased_id')
            ->addFieldToSelect('order_item_id')
            ->addFieldToFilter('main_table.purchased_id', ['in' => $ids]);

        return $linkPurchasedCollection;
    }
}
