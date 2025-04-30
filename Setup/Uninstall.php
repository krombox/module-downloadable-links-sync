<?php

declare(strict_types=1);

namespace Krombox\DownloadableLinksSync\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();
        $connection = $setup->getConnection();
        $table = 'krombox_downloadable_links_sync_queue';

        if ($connection->isTableExists($table)) {
            $connection->dropTable($table);
        }

        $setup->endSetup();
    }
}
