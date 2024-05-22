<?php

namespace Krombox\DownloadableLinksSync\Model;

use Krombox\DownloadableLinksSync\Api\MessageInterface;

class MessageManager
{
    public function __construct(
        private \Krombox\DownloadableLinksSync\Model\Link\Publisher $publisher,
        private \Krombox\DownloadableLinksSync\Api\MessageInterfaceFactory $messageFactory
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
    private function prepareMessage(string $action, array $ids, int $linkId): MessageInterface
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
