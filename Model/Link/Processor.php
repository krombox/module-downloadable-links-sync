<?php

namespace Krombox\DownloadableLinksSync\Model\Link;

use Krombox\DownloadableLinksSync\Api\MessageInterface;
use Magento\Framework\Exception\NotFoundException;

class Processor
{
    /**
     * @param ProcessorPool $processorPool
     */
    public function __construct(
        private ProcessorPool $processorPool
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
    public function execute(MessageInterface $message): void
    {
        $processor = $this->processorPool->get($message->getAction());
        $processor->process($message);
    }
}
