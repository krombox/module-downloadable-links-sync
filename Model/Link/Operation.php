<?php

namespace Krombox\DownloadableLinksSync\Model\Link;

use Krombox\DownloadableLinksSync\Model\Link\Processor\ProcessorInterface;
use Krombox\DownloadableLinksSync\Model\Link\Provider\ProviderInterface;
use Krombox\DownloadableLinksSync\Model\Link\Resolver\ResolverInterface;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\Link;

class Operation implements OperationInterface
{
    public function __construct(
        private readonly string $name,
        private readonly ResolverInterface $resolver,
        private readonly ProcessorInterface $processor,
        private readonly ProviderInterface $linkProvider
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolve(Link $link): array
    {
        return $this->resolver->resolve($link);
    }

    /**
     * @inheritDoc
     */
    public function getLinks(Product $product): array
    {
        return $this->linkProvider->getLinks($product);
    }

    /**
     * @inheritDoc
     */
    public function process($message): void
    {
        $this->processor->process($message);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }
}
