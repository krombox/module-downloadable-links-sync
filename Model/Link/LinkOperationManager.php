<?php

namespace Krombox\DownloadableLinksSync\Model\Link;

use Krombox\DownloadableLinksSync\Api\MessageInterface;
use Krombox\DownloadableLinksSync\Model\Link\Storage\StorageInterface;
use Krombox\DownloadableLinksSync\Service\ProductTypeChecker;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\Link;

class LinkOperationManager
{

    public function __construct(
        private readonly OperationPool $operationPool,
        private readonly StorageInterface $storage,
        private readonly ProductTypeChecker $productTypeChecker
    ) {
    }

    public function syncProductLinks(Product $product): void
    {
        /*
         * When the last downloadable link is removed from a product,
         * Magento automatically changes its type from 'downloadable' to 'virtual'.
         *
         * To ensure the last link can still be removed from existing orders,
         * this case must be explicitly handled, even if the product is now virtual.
         */
        if ($this->productTypeChecker->isSyncable($product)) {
            foreach ($this->operationPool->getAll() as $operation) {
                $links = $operation->getLinks($product);

                foreach ($links as $link) {
                    $this->syncLink($link, $operation);
                }
            }
        }
    }

    public function syncLink(Link $link, OperationInterface|string $operation): void
    {
        if (is_string($operation)) {
            $operation = $this->operationPool->get($operation);
        }

        $ids = $operation->resolve($link);
        $this->storage->store($operation->getName(), $ids, $link);
    }

    public function processMessage(MessageInterface $message)
    {
        $operation = $this->operationPool->get($message->getAction());
        $operation->process($message);
    }
}
