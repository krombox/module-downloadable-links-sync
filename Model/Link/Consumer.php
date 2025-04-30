<?php

namespace Krombox\DownloadableLinksSync\Model\Link;

use Krombox\DownloadableLinksSync\Api\MessageInterface;

class Consumer
{
    /**
     * @param LinkOperationManager $linkOperationManager
     */
    public function __construct(
        private readonly LinkOperationManager $linkOperationManager
    ) {
    }

    /**
     * Message queue consumer process method.
     *
     * @param MessageInterface $message
     *
     * @return void
     */
    public function process(MessageInterface $message): void
    {
        $this->linkOperationManager->processMessage($message);
    }
}
