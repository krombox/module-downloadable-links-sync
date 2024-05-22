<?php

namespace Krombox\DownloadableLinksSync\Model\Link;

use Krombox\DownloadableLinksSync\Model\Link\Processor\ProcessorInterface;
use Magento\Framework\Exception\NotFoundException;

class ProcessorPool
{
    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(
        private array $processors = []
    ) {
        foreach ($processors as $processor) {
            if (!$processor instanceof ProcessorInterface) {
                throw new \InvalidArgumentException(__(
                    'Processor %1 must be instance of %2',
                    get_class($processor),
                    ProcessorInterface::class
                ));
            }
        }
    }

    /**
     * Method get
     *
     * @param string $processorTypeCode
     *
     * @return ProcessorInterface
     * @throws NotFoundException
     */
    public function get(string $processorTypeCode): ProcessorInterface
    {
        if (!isset($this->processors[$processorTypeCode])) {
            throw new NotFoundException(
                __('The "%1" processor isn\'t defined. Verify the processor and try again.', $processorTypeCode)
            );
        }

        return $this->processors[$processorTypeCode];
    }
}
