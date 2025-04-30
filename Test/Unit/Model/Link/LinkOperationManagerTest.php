<?php

// @phpstan-ignore-file
declare(strict_types=1);

namespace Krombox\DownloadableLinksSync\Test\Unit\Model\Link;

use Krombox\DownloadableLinksSync\Api\MessageInterface;
use Krombox\DownloadableLinksSync\Model\Link\LinkOperationManager;
use Krombox\DownloadableLinksSync\Model\Link\OperationInterface;
use Krombox\DownloadableLinksSync\Model\Link\OperationPool;
use Krombox\DownloadableLinksSync\Model\Link\Storage\StorageInterface;
use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\Product\Type;
use PHPUnit\Framework\TestCase;

class LinkOperationManagerTest extends TestCase
{
    private OperationPool $operationPoolMock;
    private StorageInterface $storageMock;
    private LinkOperationManager $manager;

    protected function setUp(): void
    {
        $this->operationPoolMock = $this->createMock(OperationPool::class);
        $this->storageMock = $this->createMock(StorageInterface::class);

        $this->manager = new LinkOperationManager(
            $this->operationPoolMock,
            $this->storageMock
        );
    }

    public function testSyncProductLinksSkipsNonDownloadable(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('getTypeId')->willReturn('simple');

        $this->operationPoolMock->expects($this->never())->method('getAll');

        $this->manager->syncProductLinks($product);
    }

    public function testSyncProductLinksForDownloadable(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('getTypeId')->willReturn(Type::TYPE_DOWNLOADABLE);

        $operation = $this->createMock(OperationInterface::class);
        $linkMock = $this->createMock(\Magento\Downloadable\Model\Link::class);

        $operation->method('getLinks')->with($product)->willReturn([$linkMock]);
        $operation->method('resolve')->with($linkMock)->willReturn(['1']);
        $operation->method('getName')->willReturn('downloadable');

        $this->operationPoolMock->method('getAll')->willReturn([$operation]);

        $this->storageMock
            ->expects($this->once())
            ->method('store')
            ->with('downloadable', ['1'], $linkMock);

        $this->manager->syncProductLinks($product);
    }

    public function testSyncLinkWithOperationInstance(): void
    {
        $linkMock = $this->createMock(\Magento\Downloadable\Model\Link::class);
        $operation = $this->createMock(OperationInterface::class);
        $operation->method('resolve')->with($linkMock)->willReturn(['1']);
        $operation->method('getName')->willReturn('sync');

        $this->storageMock
            ->expects($this->once())
            ->method('store')
            ->with('sync', ['1'], $linkMock);

        $this->manager->syncLink($linkMock, $operation);
    }

    public function testSyncLinkWithOperationName(): void
    {
        $linkMock = $this->createMock(\Magento\Downloadable\Model\Link::class);
        $operation = $this->createMock(OperationInterface::class);

        $operation->method('resolve')->willReturn(['2']);
        $operation->method('getName')->willReturn('add');

        $this->operationPoolMock->method('get')->with('add')->willReturn($operation);

        $this->storageMock
            ->expects($this->once())
            ->method('store')
            ->with('add', ['2'], $linkMock);

        $this->manager->syncLink($linkMock, 'add');
    }

    public function testProcessMessage(): void
    {
        $message = $this->createMock(MessageInterface::class);
        $message->method('getAction')->willReturn('test');

        $operation = $this->createMock(OperationInterface::class);
        $this->operationPoolMock->method('get')->with('test')->willReturn($operation);

        $operation->expects($this->once())->method('process')->with($message);

        $this->manager->processMessage($message);
    }
}
