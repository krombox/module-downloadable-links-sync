<?php


namespace Krombox\DownloadableLinksSync\Model\Link\Resolver;

use Krombox\DownloadableLinksSync\Model\Link\Manager;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Link\Purchased\Item;
use Magento\Framework\DataObject\Copy;
use Magento\Store\Api\StoreRepositoryInterface;

class Update implements ResolverInterface
{
    public function __construct(
        private readonly Copy $objectCopyService,
        private readonly Manager $linkManager,
        private readonly StoreRepositoryInterface $storeRepository
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolve(Link $link): array
    {
        $linkPurchasedItemIds = [];

        foreach ($this->storeRepository->getList() as $store) {
            $storeId = $store->getId();
            // Load link for the exact store ID
            $linkToUpdate = $this->linkManager->getLink($link->getId(), $storeId);

            // If link removed stop further processing
            if (!$linkToUpdate) {
                continue;
            }

            $linkPurchasedItemIds[] = $this->getLinkPurchasedItemIdsToUpdate($linkToUpdate, $storeId);
        }

        return array_merge(...$linkPurchasedItemIds);
    }
    /**
     * @param Link $link
     * @param int $storeId
     *
     * @return array<string>
     */
    private function getLinkPurchasedItemIdsToUpdate(Link $link, int $storeId): array
    {
        $linkPurchasedItemIds = [];
        $linkPurchasedItemCollection =
            $this->linkManager->getLinkPurchasedItemCollectionByLinkId($link->getId(), $storeId);

        foreach ($linkPurchasedItemCollection as $linkPurchasedItem) {
            // Link updated check
            if ($this->hasChanges($link, $linkPurchasedItem)) {
                $linkPurchasedItemIds[] = $linkPurchasedItem->getData('item_id');
            }
        }

        return $linkPurchasedItemIds;
    }

    private function hasChanges(Link $link, Item $linkPurchasedItem): bool
    {
        $this->objectCopyService->copyFieldsetToTarget(
            'downloadable_sales_copy_link',
            'to_purchased',
            $link,
            $linkPurchasedItem
        );
        /** This value doesn`t copy in previous step */
        $linkPurchasedItem->setNumberOfDownloadsBought($link->getNumberOfDownloads());

        /** Link updated check */
        return $linkPurchasedItem->getOrigData() !== $linkPurchasedItem->getData();
    }
}
