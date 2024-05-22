<?php

namespace Krombox\DownloadableLinksSync\Model\Link;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;

class Resolver
{
    /**
     * @param ResolverPool $linkResolverPool
     */
    public function __construct(
        private ResolverPool $linkResolverPool
    ) {
    }

    /**
     * Method execute
     *
     * @param Product $product
     *
     * @return void
     */
    public function execute(Product $product): void
    {
        if ($product->getTypeId() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            foreach ($this->linkResolverPool->getAll() as $resolver) {
                $resolver->resolve($product);
            }
        }
    }
}
