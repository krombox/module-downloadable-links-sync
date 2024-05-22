<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Resolver;

use Krombox\DownloadableLinksSync\Model\Link\Handler;
use Krombox\DownloadableLinksSync\Model\Link\Manager;

class Delete implements ResolverInterface
{
    public function __construct(
        private Manager $linkManager,
        private Handler $linkHandler
    ) {
    }

    public function resolve($product): void
    {
        $productLinksToRemove = $this->linkManager->getProductLinksToRemove($product);

        foreach ($productLinksToRemove as $link) {
            $this->linkHandler->execute($link, Handler\Delete::ACTION_NAME);
        }
    }
}
