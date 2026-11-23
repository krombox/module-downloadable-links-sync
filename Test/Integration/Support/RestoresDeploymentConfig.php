<?php
declare(strict_types=1);

namespace Krombox\DownloadableLinksSync\Test\Integration\Support;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * HACK: temporary workaround until integration tests migrate to Magento's
 * attribute-based fixtures.
 *
 * Some fixtures (e.g. downloadable product links) call DomainManager::addDomains(),
 * which rewrites env.php. Magento's DeploymentConfig isolation listener fails the
 * test on any deployment-config diff. This trait snapshots env.php before fixtures
 * load (call backupDeploymentConfig() from setUpBeforeClass) and restores it before
 * the listener's endTest check (call restoreDeploymentConfig() from tearDown).
 */
trait RestoresDeploymentConfig
{
    private static ?string $envPhpFile = null;

    private static ?string $envPhpBackup = null;

    private static function backupDeploymentConfig(): void
    {
        $directoryList = Bootstrap::getObjectManager()->get(DirectoryList::class);
        self::$envPhpFile = $directoryList->getPath(DirectoryList::CONFIG) . '/env.php';
        self::$envPhpBackup = is_file(self::$envPhpFile)
            ? (string) file_get_contents(self::$envPhpFile)
            : null;
    }

    private static function restoreDeploymentConfig(): void
    {
        if (self::$envPhpFile !== null && self::$envPhpBackup !== null) {
            file_put_contents(self::$envPhpFile, self::$envPhpBackup);
        }
    }
}
