<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Storage;

use Magento\Downloadable\Model\Link;

interface StorageInterface
{
    /**
     * @param string $action
     * @param string[] $ids
     * @param Link $link
     *
     * @return void
     */
    public function store(string $action, array $ids, Link $link): void;
}
