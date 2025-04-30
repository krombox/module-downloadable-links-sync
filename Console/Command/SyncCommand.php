<?php

declare(strict_types=1);

namespace Krombox\DownloadableLinksSync\Console\Command;

use Krombox\DownloadableLinksSync\Model\Link\Queue\ForceQueueProcessor;
use Krombox\DownloadableLinksSync\Model\Link\Queue\QueueGenerator;
use Krombox\DownloadableLinksSync\Model\Link\Queue\QueueService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends Command
{
    private const PRODUCT_IDS = 'product-ids';

    public function __construct(
        private readonly QueueGenerator $queueGenerator,
        private readonly ForceQueueProcessor $forceQueueProcessor,
        private readonly QueueService $queueService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('krombox:downloadable_links:sync');
        $this->setDescription('Sync downloadable links for existing orders');
        $this->addOption(
            self::PRODUCT_IDS,
            null,
            InputOption::VALUE_OPTIONAL,
            'Product ids or range to sync separated by comma. Example: like "1-5,8,10-12"'
        );

        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $productIds = $this->parseProductIds($input->getOption(self::PRODUCT_IDS));

        $output->writeln('<comment>Generating queue...</comment>');
        $this->queueGenerator->generate($productIds);

        if ($this->queueService->isQueueEmpty()) {
            $output->writeln('<comment>No items found in the queue. Nothing to sync.</comment>');
            return Command::SUCCESS;
        }

        $output->writeln('<comment>Starting downloadable link sync...</comment>');

        $this->forceQueueProcessor->process($output);

        $output->writeln(PHP_EOL . '<comment>Done!</comment>');

        return Command::SUCCESS;
    }

    /**
     * Converts a string like "1-5,8,10-12" into an array of integers.
     */
    private function parseProductIds(string $range): array
    {
        $ids = [];

        foreach (explode(',', $range) as $part) {
            $part = trim($part);

            if (str_contains($part, '-')) {
                [$start, $end] = explode('-', $part);
                $start = (int)$start;
                $end = (int)$end;

                if ($start > $end) {
                    [$start, $end] = [$end, $start]; // Swap if reversed
                }

                for ($i = $start; $i <= $end; $i++) {
                    $ids[] = $i;
                }
            } elseif (is_numeric($part)) {
                $ids[] = (int)$part;
            }
        }

        return array_unique($ids);
    }
}
