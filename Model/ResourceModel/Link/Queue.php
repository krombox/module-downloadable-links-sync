<?php

namespace Krombox\DownloadableLinksSync\Model\ResourceModel\Link;

use Krombox\DownloadableLinksSync\Api\Data\QueueInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

//use Krombox\DownloadableLinksSync\Model\Link\Queue as QueueModel;

class Queue extends AbstractDb
{

    public const TABLE_NAME = 'krombox_downloadable_links_sync_queue';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, QueueInterface::QUEUE_ID);
    }

    /**
     * @param int[] $ids
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByIds(array $ids = []): void
    {
        $this->getConnection()->delete($this->getMainTable(), [QueueInterface::QUEUE_ID . ' in (?) ' => $ids]);
    }

    public function clear(): void
    {
        $this->getConnection()->delete($this->getMainTable());
    }
//
//    public function setProcessedByIds(array $ids = []): void
//    {
//        $now = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
//        $this->getConnection()->update($this->getMainTable(), [QueueModel::IS_PROCESSED => 1, QueueModel::PROCESSED_AT => $now], [QueueModel::QUEUE_ID . ' in (?) ' => $ids]);
//    }
}
