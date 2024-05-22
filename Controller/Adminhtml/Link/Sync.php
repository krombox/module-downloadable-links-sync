<?php

namespace Krombox\DownloadableLinksSync\Controller\Adminhtml\Link;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Sync downloadable links Controller
 */
class Sync extends Action implements HttpPostActionInterface
{
    public function __construct(
        Context $context,
        private ProductRepositoryInterface $productRepository,
        private \Krombox\DownloadableLinksSync\Model\Link\Resolver $linkResolver,
        private \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $productId = $this->getRequest()->getParam('product_id');
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->getById($productId);
        try {
            $this->linkResolver->execute($product);
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
