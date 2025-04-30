<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Queue;

use Krombox\DownloadableLinksSync\Api\Data\QueueInterface;
use Krombox\DownloadableLinksSync\Api\MessageInterface;
use Krombox\DownloadableLinksSync\Model\MessageManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
* Converts QueueInterface data to MessageInterface. Using during CLI command execution
 */
class QueueToMessageConverter
{

    /**
     * @param MessageManager $messageManager
     * @param Json $jsonSerializer
     */
    public function __construct(
        private readonly MessageManager $messageManager,
        private readonly Json $jsonSerializer
    ) {
    }

    /**
     * Convert queue data to a message object.
     *
     * @param QueueInterface $queue
     * @return MessageInterface
     */
    public function convert(QueueInterface $queue)
    {
        return $this->messageManager->prepareMessage(
            $queue->getAction(),
            $this->jsonSerializer->unserialize($queue->getIds()),
            $queue->getLinkId()
        );
    }
}
