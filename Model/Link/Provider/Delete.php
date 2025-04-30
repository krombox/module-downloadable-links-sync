<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Provider;

use Krombox\DownloadableLinksSync\Model\Link\Manager;

/**
 * Provides downloadable links that should be removed for a given product.
 */
class Delete implements ProviderInterface
{
    public function __construct(
        private readonly Manager $linkManager
    ) {
    }

    public function getLinks($product)
    {
        return $this->linkManager->getProductLinksToRemove($product);
    }
}
