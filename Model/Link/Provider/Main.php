<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Provider;

use Krombox\DownloadableLinksSync\Model\Link\Manager;

/**
 * Provides all downloadable links that should be processed for a given product.
 */
class Main implements ProviderInterface
{
    public function __construct(
        private readonly Manager $linkManager
    ) {
    }

    public function getLinks($product)
    {
        return $this->linkManager->getProductLinks($product);
    }
}
