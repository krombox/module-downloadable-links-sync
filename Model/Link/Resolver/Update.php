<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Resolver;

use Krombox\DownloadableLinksSync\Model\Link\Handler;
use Krombox\DownloadableLinksSync\Model\Link\Manager;

class Update implements ResolverInterface
{
    public function __construct(
        private Manager $linkManager,
        private Handler $linkHandler
    ) {
    }

    public function resolve($product): void
    {
        $productLinks = $this->linkManager->getProductLinks($product);

        foreach ($productLinks as $link) {
            $this->linkHandler->execute($link, Handler\Update::ACTION_NAME);
        }
    }
}
