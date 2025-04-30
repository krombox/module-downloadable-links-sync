<?php

namespace Krombox\DownloadableLinksSync\Model\Link;

use InvalidArgumentException;
use Laminas\Crypt\Exception\NotFoundException;

class OperationPool
{
    /**
     * @param OperationInterface[] $operations
     */
    public function __construct(
        private readonly array $operations = []
    ) {
        foreach ($operations as $operation) {
            if (!$operation instanceof OperationInterface) {
                throw new InvalidArgumentException(__(
                    'Operation %1 must be instance of %2',
                    get_class($operation),
                    OperationInterface::class
                ));
            }
        }
    }

    public function get(string $name): OperationInterface
    {
        if (!isset($this->operations[$name])) {
            throw new NotFoundException(
                __('The "%1" operation isn\'t defined. Verify the handler and try again.', $name)
            );
        }

        return $this->operations[$name];
    }

    /**
     * Get all operations.
     *
     * @return OperationInterface[]
     */
    public function getAll(): array
    {
        return $this->operations;
    }
}
