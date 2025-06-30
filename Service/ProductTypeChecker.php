<?php

namespace Krombox\DownloadableLinksSync\Service;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Downloadable\Model\Product\Type as DownloadableProductType;

class ProductTypeChecker
{
    public function isSyncable(ProductInterface $product): bool
    {
        $productType = $product->getTypeId();
        
        return (
            $productType === DownloadableProductType::TYPE_DOWNLOADABLE ||
            $productType === ProductType::TYPE_VIRTUAL
        );
    }
}
