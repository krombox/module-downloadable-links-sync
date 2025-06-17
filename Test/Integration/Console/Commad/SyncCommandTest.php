<?php
declare(strict_types=1);

namespace Krombox\DownloadableLinksSync\Test\Integrational\Console\Command;

use Krombox\DownloadableLinksSync\Model\Link\Manager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Command\Command;
use Krombox\DownloadableLinksSync\Console\Command\SyncCommand;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Model\StoreFactory;

class SyncCommandTest extends TestCase
{
    private $productRepository;

    private $syncCommand;

    private $linkManager;

    private $storeManager;

    private const DELETED_LINK_ID = 9999;

    public static function setUpBeforeClass(): void
    {
        self::createSecondStore();
    }

    /**
     * Create store here, instead of fixtures
     *
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private static function createSecondStore()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $objectManager->get(StoreManagerInterface::class);
        /** @var StoreFactory $storeFactory */
        $storeFactory = $objectManager->get(StoreFactory::class);
        /** @var StoreResource $storeResource */
        $storeResource = $objectManager->get(StoreResource::class);
        $storeCode = 'fixturestore';

        try {
            $store = $storeManager->getStore($storeCode);
        } catch (NoSuchEntityException $e) {
            $store = $storeFactory->create();
            $store->setCode($storeCode)
                ->setWebsiteId($storeManager->getWebsite()->getId())
                ->setGroupId($storeManager->getWebsite()->getDefaultGroupId())
                ->setName('Fixture Store')
                ->setSortOrder(10)
                ->setIsActive(1);
            $storeResource->save($store);
        }
    }

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->syncCommand = $objectManager->get(SyncCommand::class);
        $this->linkManager = $objectManager->get(Manager::class);
        $this->storeManager = $objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture loadFixture
     * @magentoDbIsolation enabled
     */
    public function testExecuteWithProductIds(): void
    {
        foreach ($this->storeManager->getStores() as $store) {
            $product = $this->productRepository->get('downloadable-product-two-store', false, $store->getId());
            $this->assertInstanceOf(Command::class, $this->syncCommand);

            $commandTester = new CommandTester($this->syncCommand);
            // By the end of the command process all product links should be aligned
            $commandTester->execute([
                '--product-ids' => $product->getId()
            ]);

            // Assert command was successful
            $this->assertEquals(Command::SUCCESS, $commandTester->getStatusCode());
            $downloadableLinks = $product->getTypeInstance()->getLinks($product);

            foreach ($downloadableLinks as $link) {
                $link = $this->linkManager->getLink((int)$link->getId(), (int)$store->getId());
                $linkPurchasedItemCollection =
                    $this->linkManager->getLinkPurchasedItemCollectionByLinkId((int)$link->getId(), (int)$store->getId());

                /* At this point, new links added to the product should be synced with existing orders. */
                $this->assertGreaterThan(
                    0,
                    $linkPurchasedItemCollection->getSize(),
                    'There should be at least 1 purchased link. This verifies that new product links have been
                    successfully synced to existing orders.');

                /* Compare existing product downloadable link data with purchased links */
                foreach ($linkPurchasedItemCollection as $linkPurchasedItem) {
                    // compare title
                    $this->assertEquals($link->getTitle(), $linkPurchasedItem->getLinkTitle());
                    // compare link url
                    $this->assertEquals($link->getLinkUrl(), $linkPurchasedItem->getLinkUrl());
                    // compare is shareable
                    $this->assertEquals($link->getIsShareable(), $linkPurchasedItem->getIsShareable());
                }
            }

            /* Deleted link from product should trigger delete that from existing orders. The count should be 0 */
            $linkPurchasedItemCollectionForDeletedLink =
                $this->linkManager->getLinkPurchasedItemCollectionByLinkId(self::DELETED_LINK_ID, (int)$store->getId());
            $this->assertEquals(0, $linkPurchasedItemCollectionForDeletedLink->getSize());
        }
    }

    public static function loadFixture()
    {
        include __DIR__ . '/../../_files/order_item_with_downloadable_product.php';
        include __DIR__ . '/../../_files/downloadable_purchased_item_links.php';
    }
}
