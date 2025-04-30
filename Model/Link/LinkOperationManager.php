<?php

namespace Krombox\DownloadableLinksSync\Model\Link;

use Krombox\DownloadableLinksSync\Api\MessageInterface;
use Krombox\DownloadableLinksSync\Model\Link\Storage\StorageInterface;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\Product\Type;

class LinkOperationManager
{

    public function __construct(
        private readonly OperationPool $operationPool,
        private readonly StorageInterface $storage
    ) {
    }

    public function syncProductLinks(Product $product): void
    {
        if ($product->getTypeId() === Type::TYPE_DOWNLOADABLE) {
            foreach ($this->operationPool->getAll() as $operation) {
                $links = $operation->getLinks($product);

                foreach ($links as $link) {
                    $this->syncLink($link, $operation);
                }
            }
        }
    }

    public function syncLink($link, OperationInterface|string $operation): void
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
