<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Resolver;

use Krombox\DownloadableLinksSync\Model\Link\Manager;
use Magento\Downloadable\Model\Link;

class Delete implements ResolverInterface
{
    /**
     * @param Manager $linkManager The link manager
     */
    public function __construct(
        private readonly Manager $linkManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolve(Link $link): array
    {
        return $this->linkManager->getLinkPurchasedItemCollectionByLinkId($link->getId())->getAllIds();
    }
}
