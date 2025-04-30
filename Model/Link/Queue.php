<?php

namespace Krombox\DownloadableLinksSync\Model\Link;

use Krombox\DownloadableLinksSync\Api\Data\QueueInterface;
use Magento\Framework\Model\AbstractModel;

class Queue extends AbstractModel implements QueueInterface
{
    protected function _construct()
    {
        $this->_init(\Krombox\DownloadableLinksSync\Model\ResourceModel\Link\Queue ::class);
    }

    public function getQueueId(): int
    {
        return $this->getData(self::QUEUE_ID);
    }

    public function setQueueId(int $queueId): QueueInterface
    {
        return $this->setData(self::QUEUE_ID, $queueId);
    }

    public function getProductId(): ?int
    {
        return $this->getData(self::PRODUCT_ID);
    }

    public function setProductId(int $productId): QueueInterface
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    public function getLinkId(): ?int
    {
        return $this->getData(self::LINK_ID);
    }

    public function setLinkId(int $linkId): QueueInterface
    {
        return $this->setData(self::LINK_ID, $linkId);
    }

    public function getAction(): ?string
    {
        return $this->getData(self::ACTION);
    }

    public function setAction(string $action): QueueInterface
    {
        return $this->setData(self::ACTION, $action);
    }

    public function getIds(): string
    {
        return $this->getData(self::IDS);
    }

    public function setIds(string $ids): QueueInterface
    {
        return $this->setData(self::IDS, $ids);
    }
}
