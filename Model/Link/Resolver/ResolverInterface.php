<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Resolver;

use Magento\Downloadable\Model\Link;

interface ResolverInterface
{
    /**
     * Resolve ids that need to be processed for the given link.
     *
     * @param Link $link
     *
     * @return string[]
     */
    public function resolve(Link $link): array;
}
