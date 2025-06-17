<?php

use Magento\TestFramework\Helper\Bootstrap;

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();

// Load downloadable product
/** @var $productRepository \Magento\Catalog\Model\ProductRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
$product = $productRepository->get('downloadable-product-two-store');
$extension = $product->getExtensionAttributes();
$links = $extension->getDownloadableProductLinks();

$customerRepository = $objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
$customer = $customerRepository->getById(1);

// Load the order 1
$orderFactory = $objectManager->get(\Magento\Sales\Model\OrderFactory::class);
$orderResource = $objectManager->get(\Magento\Sales\Model\Spi\OrderResourceInterface::class);
$order = $orderFactory->create();
$orderResource->load($order, '100000001', \Magento\Sales\Api\Data\OrderInterface::INCREMENT_ID);

// Add downloadable purchased entry manually
/** @var \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory */
$purchasedFactory = $objectManager->get(\Magento\Downloadable\Model\Link\PurchasedFactory::class);
/** @var \Magento\Downloadable\Model\Link\Purchased\ItemFactory $purchasedItemFactory */
$purchasedItemFactory = $objectManager->get(\Magento\Downloadable\Model\Link\Purchased\ItemFactory::class);

$purchased = $purchasedFactory->create();
$purchased->setOrderId($order->getId())
    ->setOrderIncrementId($order->getIncrementId())
    ->setOrderItemId($order->getItemsCollection()->getFirstItem()->getId())
    ->setCustomerId($customer->getId())
    ->setProductName($product->getName())
    ->setProductSku($product->getSku())
    ->setLinkSectionTitle('Downloads')
    ->save();

// Link update
$linkToUpdate = $links[0];
$purchasedItemToUpdate = $purchasedItemFactory->create();
$purchasedItemToUpdate
    ->setLinkId($linkToUpdate->getId())
    ->setPurchasedId($purchased->getId())
    ->setOrderItemId($order->getItemsCollection()->getFirstItem()->getId())
    ->setLinkHash(uniqid())
    ->setNumberOfDownloadsBought(100)
    ->setNumberOfDownloadsUsed(0)
    ->setLinkTitle('Link title to update')
    ->setLinkUrl('http://example.com/link_to_update.txt')
    ->setLinkType($linkToUpdate->getLinkType())
    ->setStatus('available')
    ->setProductId($product->getId())
    ->setIsShareable(0)
    ->save();

// Link delete - NON existed
$purchasedItemToDelete = $purchasedItemFactory->create();
$purchasedItemToDelete
    ->setLinkId(9999)
    ->setPurchasedId($purchased->getId())
    ->setOrderItemId($order->getItemsCollection()->getFirstItem()->getId())
    ->setLinkHash(uniqid())
    ->setNumberOfDownloadsBought(100)
    ->setNumberOfDownloadsUsed(0)
    ->setLinkTitle('Link title to delete')
    ->setLinkUrl('http://example.com/link_to_delete.txt')
    ->setLinkType(\Magento\Downloadable\Helper\Download::LINK_TYPE_URL)
    ->setStatus('available')
    ->setProductId($product->getId())
    ->save();


// Load the order 2
$order2 = $orderFactory->create();
$orderResource->load($order2, '100000002', \Magento\Sales\Api\Data\OrderInterface::INCREMENT_ID);

$purchased2 = $purchasedFactory->create();
$purchased2->setOrderId($order2->getId())
    ->setOrderIncrementId($order2->getIncrementId())
    ->setOrderItemId($order2->getItemsCollection()->getFirstItem()->getId())
    ->setCustomerId($customer->getId())
    ->setProductName($product->getName())
    ->setProductSku($product->getSku())
    ->setLinkSectionTitle('Downloads')
    ->save();


// Link update
$linkToUpdate = $links[0];
$purchasedItemToUpdate = $purchasedItemFactory->create();
$purchasedItemToUpdate
    ->setLinkId($linkToUpdate->getId())
    ->setPurchasedId($purchased2->getId())
    ->setOrderItemId($order2->getItemsCollection()->getFirstItem()->getId())
    ->setLinkHash(uniqid())
    ->setNumberOfDownloadsBought(100)
    ->setNumberOfDownloadsUsed(0)
    ->setLinkTitle('Link title to update order 100000002')
    ->setLinkUrl('http://example.com/link_to_update.txt')
    ->setLinkType($linkToUpdate->getLinkType())
    ->setStatus('available')
    ->setProductId($product->getId())
    ->setIsShareable(0)
    ->save();
