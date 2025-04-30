<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Processor;

use Krombox\DownloadableLinksSync\Api\MessageInterface;
use Magento\Framework\App\ResourceConnection;

class Delete implements ProcessorInterface
{
    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {
    }

    /**
     * Method process
     *
     * @param MessageInterface $message
     *
     * @return void
     */
    public function process(MessageInterface $message): void
    {
        $this->delete($message->getIds());
    }

    /**
     * @param string[] $ids
     *
     * @return void
     */
    private function delete(array $ids): void
    {
        $this->resourceConnection->getConnection()->delete(
            $this->resourceConnection->getTableName('downloadable_link_purchased_item'),
            ['item_id in (?)' => $ids]
        );
    }
}
