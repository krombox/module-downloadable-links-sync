<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Handler;

use Krombox\DownloadableLinksSync\Model\Config;
use Krombox\DownloadableLinksSync\Model\Link\Processor;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Link\Purchased\Item;
use Magento\Framework\DataObject\Copy;
use Magento\Store\Api\StoreRepositoryInterface;

class Update implements HandlerInterface
{
    public const ACTION_NAME = 'update';

    public function __construct(
        private Config $config,
        private \Krombox\DownloadableLinksSync\Model\Link\Manager $linkManager,
        private \Krombox\DownloadableLinksSync\Model\MessageManager $messageManager,
        private StoreRepositoryInterface $storeRepository
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(Link $link): void
    {
        foreach ($this->storeRepository->getList() as $store) {
            $storeId = $store->getId();
            /** Load link for the exact store ID*/
            $linkToUpdate = $this->linkManager->getLink($link->getId(), $storeId);

            /** If link removed stop further processing */
            if (!$linkToUpdate) {
                return;
            }

            $linkPurchasedItemCollection = $this->linkManager->getLinkPurchasedItemCollectionByLinkId($linkToUpdate->getId(), $storeId);

            foreach (array_chunk($linkPurchasedItemCollection->getAllIds(), $this->config->getChunkSize()) as $chunkIds) {
                $this->messageManager->createMessage(Processor\Update::ACTION_NAME, $chunkIds, $linkToUpdate->getId());
            }
        }
    }
}
