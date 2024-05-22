<?php

namespace Krombox\DownloadableLinksSync\Model\Link;

use Magento\Downloadable\Model\Link;
use Magento\Framework\Exception\NotFoundException;

class Handler
{
    /**
     * @param HandlerPool $linkHandlerPool
     */
    public function __construct(
        private HandlerPool $linkHandlerPool
    ) {
    }

    /**
     * Method execute
     *
     * @param Link $link
     * @param string $actionName
     *
     * @return void
     * @throws NotFoundException
     */
    public function execute(Link $link, string $actionName)
    {
        $handler = $this->linkHandlerPool->get($actionName);
        $handler->handle($link);
    }
}
