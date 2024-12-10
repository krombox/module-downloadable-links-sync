<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Handler;

use Krombox\DownloadableLinksSync\Model\Config;
use Krombox\DownloadableLinksSync\Model\Link\Processor;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Link\Purchased\Item;
use Magento\Framework\DataObject\Copy;

class Update implements HandlerInterface
{
    public const ACTION_NAME = 'update';

    public function __construct(
        private Config $config,
        private Copy $objectCopyService,
        private \Krombox\DownloadableLinksSync\Model\Link\Manager $linkManager,
        private \Krombox\DownloadableLinksSync\Model\MessageManager $messageManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(Link $link): void
    {
        $linkPurchasedItemIds = $this->getLinkPurchasedItemIdsToUpdate($link);

        foreach (array_chunk($linkPurchasedItemIds, $this->config->getChunkSize()) as $chunkIds) {
            $this->messageManager->createMessage(Processor\Update::ACTION_NAME, $chunkIds, $link->getId());
        }
    }

    /**
     * @param Link $link
     *
     * @return string[]
     */
    private function getLinkPurchasedItemIdsToUpdate(Link $link): array
    {
        $linkPurchasedItemCollection = $this->linkManager->getLinkPurchasedItemCollectionByLinkId($link->getId());
        return $linkPurchasedItemCollection->getAllIds();
    }
}
