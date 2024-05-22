<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Handler;

use Krombox\DownloadableLinksSync\Model\Config;
use Krombox\DownloadableLinksSync\Model\Link\Processor;
use Magento\Downloadable\Api\Data\LinkInterface;

class Add implements HandlerInterface
{
    public const ACTION_NAME = 'add';

    public function __construct(
        private Config $config,
        private \Krombox\DownloadableLinksSync\Model\MessageManager $messageManager,
        private \Krombox\DownloadableLinksSync\Model\Link\Manager $linkManager,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(LinkInterface $link): void
    {
        $linkPurchasedCollection = $this->linkManager->getLinkPurchasedCollectionWhereLinkMissed($link);

        foreach (array_chunk($linkPurchasedCollection->getAllIds(), $this->config->getChunkSize()) as $chunkIds) {
            $this->messageManager->createMessage(
                Processor\Add::ACTION_NAME,
                $chunkIds,
                $link->getId()
            );
        }
    }
}
