<?php

declare(strict_types=1);

namespace Krombox\DownloadableLinksSync\Api\Data;

interface QueueInterface
{
    public const QUEUE_ID = 'queue_id';
    public const PRODUCT_ID = 'product_id';
    public const LINK_ID = 'link_id';
    public const ACTION = 'action';
    public const IDS = 'ids';

    public function getQueueId(): int;

    public function setQueueId(int $queueId): QueueInterface;

    public function getProductId(): ?int;

    public function setProductId(int $productId): QueueInterface;

    public function getLinkId(): ?int;

    public function setLinkId(int $linkId): QueueInterface;

    public function getAction(): ?string;

    public function setAction(string $action): QueueInterface;

    public function getIds(): string;

    public function setIds(string $ids): QueueInterface;
}
