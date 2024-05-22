<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Resolver;

use Magento\Catalog\Model\Product;

interface ResolverInterface
{
    /**
     * Method resolve
     *
     * @param Product $product
     *
     * @return void
     */
    public function resolve(Product $product): void;
}
