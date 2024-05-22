<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Handler;

use Magento\Downloadable\Model\Link;

interface HandlerInterface
{
    /**
     * Method handle
     *
     * @param Link $link
     *
     * @return void
     */
    public function handle(Link $link): void;
}
