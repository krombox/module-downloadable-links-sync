<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Resolver;

use Krombox\DownloadableLinksSync\Model\Link\Manager;
use Magento\Downloadable\Api\Data\LinkInterface;

class Add implements ResolverInterface
{
    public function __construct(
        private readonly Manager $linkManager,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolve(LinkInterface $link): array
    {
        return $this->linkManager->getLinkPurchasedCollectionWhereLinkMissed($link)->getAllIds();
    }
}
