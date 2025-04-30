<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Queue;

use Krombox\DownloadableLinksSync\Api\Data\QueueInterface;
use Krombox\DownloadableLinksSync\Model\Link\Queue;
use Krombox\DownloadableLinksSync\Model\Link\QueueFactory;
use Krombox\DownloadableLinksSync\Model\ResourceModel\Link\Queue as QueueResource;
use Krombox\DownloadableLinksSync\Model\ResourceModel\Link\Queue\CollectionFactory;
use Magento\Framework\Data\Collection;

class QueueService
{
    /**
     * @param QueueResource $queueResource
     * @param QueueFactory $queueFactory
     * @param CollectionFactory $queueCollectionFactory
     */
    public function __construct(
        private readonly QueueResource $queueResource,
        private readonly QueueFactory $queueFactory,
        private readonly CollectionFactory $queueCollectionFactory
    ) {
    }

    public function createQueue(): Queue
    {
        return $this->queueFactory->create();
    }

    public function addToQueue(Queue $queue): void
    {
        $this->queueResource->save($queue);
    }

    /**
     * @param int $limit
     *
     * @return QueueInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQueues(int $limit = 10): array
    {
        $queueCollection = $this->queueCollectionFactory->create();
        $queueCollection
            ->setPageSize($limit)
            ->setOrder('product_id', Collection::SORT_ORDER_ASC)
            ->setOrder('link_id', Collection::SORT_ORDER_ASC);

        /** @var QueueInterface[] $items */
        $items = $queueCollection->getItems();

        $ids = [];
        foreach ($items as $queue) {
            $ids[] = $queue->getQueueId();
        }
        if (!empty($ids)) {
            $this->queueResource->deleteByIds($ids);
        }

        return $items;
    }

    public function isQueueEmpty(): bool
    {
        return !(bool)$this->getQueueSize();
    }

    public function getQueueSize(): int
    {
        return $this->queueCollectionFactory->create()->getSize();
    }

    public function clearQueue(): void
    {
        $this->queueResource->clear();
    }
}
