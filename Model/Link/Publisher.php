<?php

namespace Krombox\DownloadableLinksSync\Model\Link;

use Krombox\DownloadableLinksSync\Api\MessageInterface;
use Magento\Framework\MessageQueue\PublisherInterface;

class Publisher
{
    public const TOPIC_NAME = 'krombox.downloadable_links.sync';

    /**
     * @param PublisherInterface $publisher
     */
    public function __construct(
        private PublisherInterface $publisher
    ) {
    }

    /**
     * Method execute
     *
     * @param MessageInterface $message
     *
     * @return void
     */
    public function execute(MessageInterface $message): void
    {
        $this->publisher->publish(self::TOPIC_NAME, $message);
    }
}
