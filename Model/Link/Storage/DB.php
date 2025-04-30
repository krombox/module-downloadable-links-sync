<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Storage;

use Krombox\DownloadableLinksSync\Model\Config;
use Krombox\DownloadableLinksSync\Model\Link\Queue\QueueService;
use Magento\Downloadable\Model\Link;
use Magento\Framework\Serialize\Serializer\Json;

class DB implements StorageInterface
{
    /**
     * @param Config $config
     * @param QueueService $queueService
     * @param Json $jsonSerializer
     */
    public function __construct(
        private readonly Config $config,
        private readonly QueueService $queueService,
        private readonly Json $jsonSerializer
    ) {
    }

    /**
     * Stores the action and associated link information into the DB queue.
     *
     * @param string $action The action to be stored in the queue.
     * @param string[] $ids The list of IDs to be processed in chunks.
     * @param Link $link The link object containing product and link IDs.
     */
    public function store(string $action, array $ids, Link $link): void
    {
        foreach (array_chunk($ids, $this->config->getChunkSize()) as $chunkIds) {
            $queue = $this->queueService->createQueue();
            $queue->setAction($action)
                ->setProductId($link->getProductId())
                ->setLinkId($link->getId())
                ->setIds($this->jsonSerializer->serialize($chunkIds));

            $this->queueService->addToQueue($queue);
        }
    }
}
