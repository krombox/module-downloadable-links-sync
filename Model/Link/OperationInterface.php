<?php

namespace Krombox\DownloadableLinksSync\Model\Link;

use Krombox\DownloadableLinksSync\Api\MessageInterface;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\Link;

interface OperationInterface
{
    /**
     * Resolve downloadable link for operation changes and return ids required to sync with existing orders.
     *
     * @param Link $link
     *
     * @return array
     */
    public function resolve(Link $link): array;

    /**
     * Retrieve all relevant downloadable links for the specified product associated with this operation.
     *
     * @param Product $product
     *
     * @return Link[]
     */
    public function getLinks(Product $product): array;

    /**
     * Apply the operation logic based on a queue message, syncing the related data (typically with existing orders).
     *
     * @param MessageInterface $message
     *
     * @return void
     */
    public function process(MessageInterface $message): void;

    /**
     * Get the unique identifier (name) of the operation.
     *
     * @return string
     */
    public function getName(): string;
}
