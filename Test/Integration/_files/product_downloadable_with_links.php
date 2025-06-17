<?php

declare(strict_types=1);

use Magento\Downloadable\Model\LinkRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Downloadable\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Downloadable\Api\DomainManagerInterface;
use Magento\Store\Model\StoreFactory;
use Magento\Downloadable\Model\ResourceModel\Link as LinkResource;

$objectManager = Bootstrap::getObjectManager();
$linkRepository = $objectManager->get(LinkRepository::class);
/** @var DomainManagerInterface $domainManager */
$domainManager = Bootstrap::getObjectManager()->get(DomainManagerInterface::class);
$domainManager->addDomains(['example.com']);

/** @var StoreFactory $storeFactory */
$storeFactory = $objectManager->get(StoreFactory::class);
/**
 * @var \Magento\Catalog\Model\Product $product
 */
$product = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product
    ->setTypeId(ProductType::TYPE_DOWNLOADABLE)
    ->setAttributeSetId(4)
    ->setStoreId(0)
    ->setWebsiteIds([1])
    ->setName('Downloadable Product (Links can`t be purchased separately)')
    ->setSku('downloadable-product-two-store')
    ->setPrice(10)
    ->setVisibility(ProductVisibility::VISIBILITY_BOTH)
    ->setStatus(ProductStatus::STATUS_ENABLED)
    ->setStockData(
        [
            'qty' => 100,
            'is_in_stock' => 1,
            'manage_stock' => 1,
        ]
    );

$storeManager = $objectManager->get(Magento\Store\Model\StoreRepository::class);
$store2 = $storeManager->get('fixturestore');

/**
 * @var \Magento\Downloadable\Api\Data\LinkInterfaceFactory $linkFactory1
 */
$linkFactory1 = Bootstrap::getObjectManager()
    ->get(\Magento\Downloadable\Api\Data\LinkInterfaceFactory::class);
$link1 = $linkFactory1->create();
$link1
    //->setStoreId($store2->getId())
    ->setProductId($product->getId())
    ->setTitle('Downloadable Product Link 1')
    ->setLinkType(\Magento\Downloadable\Helper\Download::LINK_TYPE_URL)
    ->setIsShareable(\Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG)
    ->setLinkUrl('http://example.com/downloadable1.txt')
    ->setStoreId($product->getStoreId())
    ->setWebsiteId($product->getStore()->getWebsiteId())
    ->setProductWebsiteIds($product->getWebsiteIds())
    ->setSortOrder(10)
    ->setPrice(2.0000)
    ->setNumberOfDownloads(0);

//$link1Id = $linkRepository->save($product->getSku(), $link1);
//$link1->setId($link1Id);

/**
 * @var \Magento\Downloadable\Api\Data\LinkInterfaceFactory $linkFactory2
 */
$linkFactory2 = Bootstrap::getObjectManager()
    ->get(\Magento\Downloadable\Api\Data\LinkInterfaceFactory::class);
$link2 = $linkFactory2->create();
$link2
    //->setStoreId($store2->getId())
    ->setProductId($product->getId())
    ->setTitle('Downloadable Product Link 2')
    ->setLinkType(\Magento\Downloadable\Helper\Download::LINK_TYPE_URL)
    ->setIsShareable(\Magento\Downloadable\Model\Link::LINK_SHAREABLE_CONFIG)
    ->setLinkUrl('http://example.com/downloadable2.txt')
    ->setStoreId($product->getStoreId())
    ->setWebsiteId($product->getStore()->getWebsiteId())
    ->setProductWebsiteIds($product->getWebsiteIds())
    ->setSortOrder(20)
    ->setPrice(4.0000)
    ->setNumberOfDownloads(0);

$extension = $product->getExtensionAttributes();
$extension->setDownloadableProductLinks([$link1, $link2]);
$product->setExtensionAttributes($extension);
$product->setLinksPurchasedSeparately(true);


$productRepository = Bootstrap::getObjectManager()
    ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$productRepository->save($product);

$product = $productRepository->get($product->getSku(), true, $store2->getId()); // Load with store context

$extension = $product->getExtensionAttributes();
$links = $extension->getDownloadableProductLinks();
$link1Clone = clone $links[0];

$link1 = $objectManager->create(\Magento\Downloadable\Model\Link::class);
$link1
    ->setId($link1Clone->getId())
    ->setLinkId($link1Clone->getLinkId())
    ->setLinkType('url')
    ->setStoreId($store2->getId()) // set store is important otherwise change the title for default store
    ->setTitle('CUSTOM store title')
    ->setProductId($product->getId())
    ->setPrice(90)
    ->setSortOrder(10)
    ->setLinkUrl($link1Clone->getLinkUrl())
    ->setNumberOfDownloads(100)
;

/** @var \Magento\Downloadable\Model\ResourceModel\Link $linkResourceModel */
$linkResourceModel = $objectManager->get(LinkResource::class);
$linkResourceModel->save($link1);
