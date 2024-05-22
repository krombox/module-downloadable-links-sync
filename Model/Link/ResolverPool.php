<?php

namespace Krombox\DownloadableLinksSync\Model\Link;

use InvalidArgumentException;
use Krombox\DownloadableLinksSync\Model\Link\Resolver\ResolverInterface;

class ResolverPool
{
    /**
     * @param ResolverInterface[] $resolvers
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        private array $resolvers = []
    ) {
        foreach ($resolvers as $resolver) {
            if (!$resolver instanceof ResolverInterface) {
                throw new InvalidArgumentException(__(
                    'Resolver %1 must be instance of %2',
                    get_class($resolver),
                    ResolverInterface::class
                ));
            }
        }
    }

    /**
     * Method getAll
     *
     * @return ResolverInterface[]
     */
    public function getAll()
    {
        return $this->resolvers;
    }
}
