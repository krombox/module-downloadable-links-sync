<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Resolver;

use Krombox\DownloadableLinksSync\Model\Link\Handler;
use Krombox\DownloadableLinksSync\Model\Link\Manager;

class Add implements ResolverInterface
{
    public function __construct(
        private Manager $linkManager,
        private Handler $linkHandler
    ) {
    }

    public function resolve($product): void
    {
        $productLinksNew = $this->linkManager->getProductLinks($product);

        foreach ($productLinksNew as $link) {
            $this->linkHandler->execute($link, Handler\Add::ACTION_NAME);
        }
    }
}
