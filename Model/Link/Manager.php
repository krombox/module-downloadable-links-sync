<?php

namespace Krombox\DownloadableLinksSync\Model\Link;

use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Link\Purchased\Item as LinkPurchasedItem;
use Magento\Downloadable\Model\Link\Purchased\ItemFactory as LinkPurchasedItemFactory;
use Magento\Downloadable\Model\LinkFactory;
use Magento\Downloadable\Model\ResourceModel\Link\Collection as LinkCollection;
use Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory as LinkCollectionFactory;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Collection as LinkPurchasedCollection;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\CollectionFactory as LinkPurchasedCollectionFactory;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item as LinkPurchasedItemResource;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection as LinkPurchasedItemCollection;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory as LinkPurchasedItemCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
* Class Manager
 *
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class Manager
{
    /**
     * @param LinkFactory $linkFactory
     * @param LinkPurchasedCollectionFactory $linkPurchasedCollectionFactory
     * @param LinkPurchasedItemCollectionFactory $linkPurchasedItemCollectionFactory
     * @param LinkCollectionFactory $linkCollectionFactory
     * @param LinkPurchasedItemFactory $linkPurchasedItemFactory
     * @param LinkPurchasedItemResource $linkPurchasedItemResource
     * @param ResourceConnection $connection
     */
    public function __construct(
        private readonly LinkFactory $linkFactory,
        private readonly LinkPurchasedCollectionFactory $linkPurchasedCollectionFactory,
        private readonly LinkPurchasedItemCollectionFactory $linkPurchasedItemCollectionFactory,
        private readonly LinkCollectionFactory $linkCollectionFactory,
        private readonly LinkPurchasedItemFactory $linkPurchasedItemFactory,
        private readonly LinkPurchasedItemResource $linkPurchasedItemResource,
        private readonly ResourceConnection $connection
    ) {
    }

    /**
     * @param Product $product
     *
     * @return Link[]
     */
    public function getProductLinks(Product $product): array
    {
        /** Set downloadable links null to bypass cache/memory and load latest changes */
        $product->setDownloadableLinks(null);
        /** @var \Magento\Downloadable\Model\Product\Type $typeInstance */
        $typeInstance = $product->getTypeInstance();
        return $typeInstance->getLinks($product);
    }

    public function getProductLinksToRemove(Product $product): array
    {
        return array_diff_key($this->getProductLinksPurchased($product), $this->getProductLinks($product));
    }

    /**
     * @param $product
     *
     * @return Link[]
     */
    private function getProductLinksPurchased(Product $product): array
    {
        $productLinksPurchased = [];
        $linkPurchasedItemCollection = $this->getLinkPurchasedItemCollectionByProduct($product->getId());

        /** Searching by linkPurchasedItem because links records removed from DB after product save */
        foreach ($linkPurchasedItemCollection as $linkPurchasedItem) {
            $link = $this->linkFactory->create();
            /** @var LinkPurchasedItem $linkPurchasedItem */
            $linkId = $linkPurchasedItem->getLinkId();
            $productId = $linkPurchasedItem->getProductId();
            $link->setId($linkId);
            $link->setProductId($productId);
            $productLinksPurchased[$linkPurchasedItem->getLinkId()] = $link;
        }

        return $productLinksPurchased;
    }

    public function getLinkPurchasedItemCollectionByProduct(int $productId): LinkPurchasedItemCollection
    {
        return $this->createLinkPurchasedItemCollection()
            ->addFieldToSelect('link_id')
            ->addFieldToSelect('product_id')
            ->addFieldToFilter('product_id', ['eq' => $productId])
            ->distinct(true);
    }

    public function getLinkPurchasedItemCollection(?int $storeId = null): LinkPurchasedItemCollection
    {
        $linkPurchasedCollection = $this->createLinkPurchasedItemCollection();

        if ($storeId !== null) {
            $connection = $this->connection->getConnection();
            $orderItemTableName = $connection->getTableName('sales_order_item');

            $orderItemJoinCondition = [
                $orderItemTableName . '.' . OrderItemInterface::ITEM_ID . ' = main_table.order_item_id',
                $orderItemTableName . '.' . OrderItemInterface::STORE_ID . ' = ' . $storeId
            ];

            $linkPurchasedCollection
                ->join(
                    $orderItemTableName,
                    implode(' AND ', $orderItemJoinCondition),
                    ['order_item_qty_ordered' => OrderItemInterface::QTY_ORDERED]
                );
        }

        return $linkPurchasedCollection;
    }

    /**
     * @param int $linkId
     * @param int|null $storeId
     *
     * @return LinkPurchasedItemCollection
     */
    public function getLinkPurchasedItemCollectionByLinkId(
        int $linkId,
        ?int $storeId = null
    ): LinkPurchasedItemCollection {
        return $this->getLinkPurchasedItemCollection($storeId)
            ->addFieldToFilter('main_table.link_id', ['eq' => $linkId]);
    }

    /**
     * @param array<string> $ids
     * @param int|null $storeId
     *
     * @return LinkPurchasedItemCollection
     */
    public function getLinkPurchasedItemCollectionByIds(array $ids, ?int $storeId = null): LinkPurchasedItemCollection
    {
        return $this->getLinkPurchasedItemCollection($storeId)
            ->addFieldToFilter('main_table.item_id', ['in' => $ids]);
    }

    /**
     * @param int $productId
     *
     * @return LinkPurchasedCollection
     */
    public function getLinkPurchasedCollectionByProductId(int $productId): LinkPurchasedCollection
    {
        $connection = $this->connection->getConnection();
        $orderItemTableName = $connection->getTableName('sales_order_item');

        $orderItemJoinCondition = [
            $orderItemTableName . '.item_id = main_table.order_item_id',
            $connection->quoteInto("{$orderItemTableName}.product_id = ?", $productId),
        ];

        return $this->createLinkPurchasedCollection()
            ->join(
                $orderItemTableName,
                implode(' AND ', $orderItemJoinCondition)
            );
    }

    public function getLinkPurchasedCollectionWhereLinkMissed(Link $link): LinkPurchasedCollection
    {
        $connection = $this->connection->getConnection();
        $linkPurchasedItemTableName = $connection->getTableName('downloadable_link_purchased_item');
        $orderItemTableName = $connection->getTableName('sales_order_item');

        $linkPurchasedItemJoinCondition = [
            $linkPurchasedItemTableName . '.purchased_id = main_table.purchased_id',
            $linkPurchasedItemTableName . '.product_id = ' . $link->getProductId(),
        ];

        $orderItemJoinCondition = [
            $orderItemTableName . '.' . OrderItemInterface::ITEM_ID . ' = main_table.order_item_id',
            $orderItemTableName . '.' . OrderItemInterface::STORE_ID . ' is not NULL'
        ];

        $linkPurchasedCollection = $this->createLinkPurchasedCollection()
            ->join(
                $linkPurchasedItemTableName,
                implode(' AND ', $linkPurchasedItemJoinCondition),
                []
            ) ->join(
                $orderItemTableName,
                implode(' AND ', $orderItemJoinCondition),
                ['order_item_qty_ordered' => OrderItemInterface::QTY_ORDERED]
            );

        $linkPurchasedCollection->getSelect()
            ->having(
                new \Zend_Db_Expr('SUM(' . $linkPurchasedItemTableName . '.link_id = ' . $link->getId() . ') = 0')
            )
            ->group($linkPurchasedItemTableName . '.purchased_id');

        return $linkPurchasedCollection;
    }

    /**
     * Get first link from collection by linkId
     *
     * @param int $linkId
     *
     * @return Link|null
     */
    public function getLink(int $linkId, int $storeId = 0): Link|null
    {
        /** @var Link $link */
        $link = $this->createLinkCollection()
            ->addFieldToFilter('main_table.link_id', ['eq' => $linkId])
            ->addTitleToResult($storeId)
            //->addPriceToResult()
            ->getFirstItem();

        if (!$link->getId()) {
            return null;
        }

        return $link;
    }

    public function saveLinkPurchasedItem(\Magento\Downloadable\Model\Link\Purchased\Item $model): void
    {
        $this->linkPurchasedItemResource->save($model);
    }

    public function createLinkCollection(): LinkCollection
    {
        return $this->linkCollectionFactory->create();
    }

    public function createLinkPurchasedItemCollection(): LinkPurchasedItemCollection
    {
        return $this->linkPurchasedItemCollectionFactory->create();
    }

    public function createLinkPurchasedCollection(): LinkPurchasedCollection
    {
        return $this->linkPurchasedCollectionFactory->create();
    }

    public function createLinkPurchasedItemModel(): LinkPurchasedItem
    {
        return $this->linkPurchasedItemFactory->create();
    }
}
