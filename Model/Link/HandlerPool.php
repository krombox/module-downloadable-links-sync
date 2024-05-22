<?php

namespace Krombox\DownloadableLinksSync\Model\Link;

use Krombox\DownloadableLinksSync\Model\Link\Handler\HandlerInterface;
use Magento\Framework\Exception\NotFoundException;

class HandlerPool
{
    /**
     * @param HandlerInterface[] $handlers
     */
    public function __construct(
        private array $handlers = []
    ) {
        foreach ($handlers as $handler) {
            if (!$handler instanceof HandlerInterface) {
                throw new \InvalidArgumentException(__(
                    'Handler %1 must be instance of %2',
                    get_class($handler),
                    HandlerInterface::class
                ));
            }
        }
    }

    /**
     * Method get
     *
     * @param string $handlerTypeCode
     *
     * @return HandlerInterface
     * @throws NotFoundException
     */
    public function get(string $handlerTypeCode): HandlerInterface
    {
        if (!isset($this->handlers[$handlerTypeCode])) {
            throw new NotFoundException(
                __('The "%1" handler isn\'t defined. Verify the handler and try again.', $handlerTypeCode)
            );
        }

        return $this->handlers[$handlerTypeCode];
    }
}
