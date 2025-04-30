<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Provider;

use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\Link;

interface ProviderInterface
{
    /**
     * Provides downloadable links that should be processed for a given product.
     *
     * @param Product $product
     *
     * @return Link[]
     */
    public function getLinks(Product $product);
}
