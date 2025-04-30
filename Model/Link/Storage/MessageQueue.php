<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Storage;

use Krombox\DownloadableLinksSync\Model\Config;
use Krombox\DownloadableLinksSync\Model\MessageManager;
use Magento\Downloadable\Model\Link;

class MessageQueue implements StorageInterface
{

    /**
     * @param Config $config
     * @param MessageManager $messageManager
     */
    public function __construct(
        private readonly Config $config,
        private readonly MessageManager $messageManager
    ) {
    }

    /**
     * @param string $action
     * @param string[] $ids
     * @param Link $link
     *
     * @return void
     */
    public function store(string $action, array $ids, Link $link): void
    {
        foreach (array_chunk($ids, $this->config->getChunkSize()) as $chunkIds) {
            $this->messageManager->createMessage(
                $action,
                $chunkIds,
                $link->getId()
            );
        }
    }
}
