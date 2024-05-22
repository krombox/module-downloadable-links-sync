<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Processor;

use Krombox\DownloadableLinksSync\Api\MessageInterface;

interface ProcessorInterface
{
    /**
     * Method process
     *
     * @param MessageInterface $message
     *
     * @return void
     */
    public function process(MessageInterface $message): void;
}
