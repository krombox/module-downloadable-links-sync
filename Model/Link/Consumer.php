<?php

namespace Krombox\DownloadableLinksSync\Model\Link;

use Krombox\DownloadableLinksSync\Api\MessageInterface;
use Magento\Framework\Exception\NotFoundException;

class Consumer
{
    /**
     * @param Processor $linkProcessor
     */
    public function __construct(
        private Processor $linkProcessor
    ) {
    }

    /**
     * Method process
     *
     * @param MessageInterface $message
     *
     * @return void
     * @throws NotFoundException
     */
    public function process(MessageInterface $message): void
    {
        $this->linkProcessor->execute($message);
    }
}
