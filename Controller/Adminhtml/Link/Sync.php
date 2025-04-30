<?php

namespace Krombox\DownloadableLinksSync\Controller\Adminhtml\Link;

use Krombox\DownloadableLinksSync\Model\Link\LinkOperationManager;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Psr\Log\LoggerInterface;

/**
 * Sync downloadable links Controller
 */
class Sync extends Action implements HttpPostActionInterface
{
    /**
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        private readonly LinkOperationManager $linkOperationManager,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $resultRedirect = $this->resultRedirectFactory->create();
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->getById($productId, false, $storeId);
        try {
            $this->linkOperationManager->syncProductLinks($product);
            $message = 'Downloadable links sync queue has been generated successfully.
            It will take some time to process the updates';
            $this->messageManager->addSuccessMessage(__($message));
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath(
            'catalog/product/edit',
            ['id' => $productId, '_current' => true, 'set' => $product->getAttributeSetId()]
        );
    }
}
