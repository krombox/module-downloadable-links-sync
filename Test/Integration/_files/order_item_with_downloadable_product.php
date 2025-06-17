<?php

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Address;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreRepository;
use Magento\Catalog\Model\ProductRepository;

require __DIR__ . '/product_downloadable_with_links.php';

$objectManager = Bootstrap::getObjectManager();
$storeManager = $objectManager->get(StoreManagerInterface::class);
$productRepository = $objectManager->get(ProductRepository::class);
$addressData = include __DIR__ . '/address_data.php';

$product = $productRepository->get('downloadable-product-two-store');
$linkIds = array_keys($product->getDownloadableLinks());
$requestInfo = ['links' => $linkIds];

function createOrderAddress(array $data, string $type): Address
{
    $objectManager = Bootstrap::getObjectManager();
    $address = $objectManager->create(Address::class, ['data' => $data]);
    return $address->setAddressType($type);
}

function createOrderItem($product, array $requestInfo, int $storeId): Item
{
    $objectManager = Bootstrap::getObjectManager();
    $item = $objectManager->create(Item::class);
    $item->setProductId($product->getId());
    $item->setQtyOrdered(1);
    $item->setBasePrice($product->getPrice());
    $item->setPrice($product->getPrice());
    $item->setRowTotal($product->getPrice());
    $item->setProductType($product->getTypeId());
    $item->setProductOptions(['info_buyRequest' => $requestInfo]);
    $item->setStoreId($storeId);
    return $item;
}

function createOrder(string $incrementId, int $storeId, Address $billing, Address $shipping, Item $item): void
{
    $objectManager = Bootstrap::getObjectManager();
    $payment = $objectManager->create(Payment::class)->setMethod('checkmo');

    $order = $objectManager->create(Order::class);
    $order->setIncrementId($incrementId);
    $order->setState(Order::STATE_COMPLETE);
    $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE));
    $order->setCustomerIsGuest(true);
    $order->setCustomerEmail('customer@null.com');
    $order->setCustomerFirstname('firstname');
    $order->setCustomerLastname('lastname');
    $order->setBillingAddress($billing);
    $order->setShippingAddress($shipping);
    $order->setAddresses([$billing, $shipping]);
    $order->setPayment($payment);
    $order->addItem($item);
    $order->setStoreId($storeId);
    $order->setSubtotal(100);
    $order->setBaseSubtotal(100);
    $order->setBaseGrandTotal(100);
    $order->save();
}

// Shared addresses (clone for second order)
$billingAddress = createOrderAddress($addressData, 'billing');
$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

// Order for default store (store 1)
$defaultStoreId = $storeManager->getStore()->getId();
$item1 = createOrderItem($product, $requestInfo, $defaultStoreId);
createOrder('100000001', $defaultStoreId, $billingAddress, $shippingAddress, $item1);

// Order for second store (store 2)
$storeId2 = $objectManager->get(StoreRepository::class)->get('fixturestore')->getId();
$item2 = createOrderItem($product, $requestInfo, $storeId2);
createOrder('100000002', $storeId2, clone $billingAddress, clone $shippingAddress, $item2);
