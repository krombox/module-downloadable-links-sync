<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Handler;

use Krombox\DownloadableLinksSync\Model\Config;
use Krombox\DownloadableLinksSync\Model\Link\Processor;
use Magento\Downloadable\Model\Link;

class Delete implements HandlerInterface
{
    public const ACTION_NAME = 'delete';

    public function __construct(
        private Config $config,
        private \Krombox\DownloadableLinksSync\Model\MessageManager $messageManager,
        private \Krombox\DownloadableLinksSync\Model\Link\Manager $linkManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(Link $link): void
    {
        $linkPurchasedItemCollection = $this->linkManager->getLinkPurchasedItemCollectionByLinkId($link->getId());

        foreach (array_chunk($linkPurchasedItemCollection->getAllIds(), $this->config->getChunkSize()) as $chunkIds) {
            $this->messageManager->createMessage(
                Processor\Delete::ACTION_NAME,
                $chunkIds,
                $link->getId()
            );
        }
    }
}
