<?php

namespace Krombox\DownloadableLinksSync\Model\Link\Queue;

use Krombox\DownloadableLinksSync\Model\Link\LinkOperationManager;
use Krombox\DownloadableLinksSync\Model\Link\Manager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ForceQueueProcessor
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly Manager $linkManager,
        private readonly QueueService $queueService,
        private readonly QueueToMessageConverter $queueToMessageConverter,
        private readonly LinkOperationManager $linkOperationManager,
    ) {
    }

    public function process(OutputInterface $output): void
    {
        $currentProductId = null;
        $currentLinkId = null;

        $progressBar = new ProgressBar($output, $this->queueService->getQueueSize());

        if (!$output->isVerbose()) {
            $progressBar->setFormat('[%bar%] %percent:3s%% %message%');
        }

        $progressBar->start();

        while (!$this->queueService->isQueueEmpty()) {
            foreach ($this->queueService->getQueues() as $queue) {
                $productId = $queue->getProductId();
                $linkId = $queue->getLinkId();

                try {
                    $product = $this->productRepository->getById($productId);
                } catch (\Exception $e) {
                    $output->writeln("<error>Failed to load product #$productId: {$e->getMessage()}</error>");
                    $progressBar->advance();
                    continue;
                }

                $message = $this->queueToMessageConverter->convert($queue);
                $link = $this->linkManager->getLink($linkId);
                $messageLinkId = $message->getLinkId();

                if ($productId !== $currentProductId) {
                    $this->updateProgressMessage($progressBar, $output, $product->getName(), 'info');
                    $currentProductId = $productId;
                }

                if ($messageLinkId !== $currentLinkId) {
                    $linkTitle = $link ? $link->getTitle() : "Link ID $messageLinkId";
                    $productNameText = $output->isVerbose() ? '' : "<info>{$product->getName()}</info> - ";
                    $messageText = "{$productNameText}<comment>{$linkTitle}</comment>";
                    $this->updateProgressMessage($progressBar, $output, $messageText);
                    $currentLinkId = $messageLinkId;
                }

                $this->linkOperationManager->processMessage($message);
                $progressBar->advance();
            }
        }

        $progressBar->setMessage('');
        $progressBar->finish();
    }

    private function updateProgressMessage(
        ProgressBar $progressBar,
        OutputInterface $output,
        string $message,
        string $style = 'comment'
    ): void {
        $progressBar->clear();

        if ($output->isVerbose()) {
            $output->writeln("<$style>$message</$style>");

        } else {
            $progressBar->setMessage("$message");
            $progressBar->display();
        }
    }
}
