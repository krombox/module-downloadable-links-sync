<?php

namespace Krombox\DownloadableLinksSync\Model;

use Krombox\DownloadableLinksSync\Api\MessageInterface;
use Krombox\DownloadableLinksSync\Api\MessageInterfaceFactory;
use Krombox\DownloadableLinksSync\Model\Link\Publisher;

class MessageManager
{
    public function __construct(
        private readonly Publisher $publisher,
        private readonly MessageInterfaceFactory $messageFactory
    ) {
    }

    /**
     * @param string $action
     * @param string[] $ids
     * @param int $linkId
     *
     * @return void
     */
    public function createMessage(string $action, array $ids, int $linkId): void
    {
        $this->publisher->execute($this->prepareMessage($action, $ids, $linkId));
    }

    /**
     * @param string $action
     * @param string[] $ids
     * @param int $linkId
     *
     * @return MessageInterface
     */
    public function prepareMessage(string $action, array $ids, int $linkId): MessageInterface
    {
        return $this->createMessageModel()
            ->setAction($action)
            ->setLinkId($linkId)
            ->setIds($ids);
    }

    private function createMessageModel(): MessageInterface
    {
        return $this->messageFactory->create();
    }
}
