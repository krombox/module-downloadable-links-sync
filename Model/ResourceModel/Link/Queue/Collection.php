<?php

namespace Krombox\DownloadableLinksSync\Model\ResourceModel\Link\Queue;

use Krombox\DownloadableLinksSync\Model\ResourceModel\Link\Queue as LinkQueueResourceModel;
use Krombox\DownloadableLinksSync\Model\Link\Queue;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            Queue::class,
            LinkQueueResourceModel::class
        );
    }
}
