<?php

namespace Krombox\DownloadableLinksSync\Plugin\Model;

use Krombox\DownloadableLinksSync\Model\Config;
use Krombox\DownloadableLinksSync\Model\Link\LinkOperationManager;
use Krombox\DownloadableLinksSync\Model\Link\Manager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Downloadable\Model\Link;

class LinkRepository
{
    public function __construct(
        private readonly LinkOperationManager $linkOperationManager,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly Manager $linkManager,
        private readonly Config $config
    ) {
    }

    private function hasDataChanges(Link $productLink, Link $linkToCompare): bool
    {
        /** Do not include price in comparison */
        $unsetAttributes = ['price'];
        /** Check for an extension attributes and unset if not empty. This prevents error on array_diff_assoc
         * comparison. Currently, do not support extension attributes changes. */
        if ($linkToCompare->getExtensionAttributes() !== null && $productLink->getExtensionAttributes() !== null) {
            $unsetAttributes = array_merge($unsetAttributes, ['extension_attributes']);
        }

        $linkToCompare->unsetData($unsetAttributes);
        $linkToCompareData = $linkToCompare->getData();
        $productLinkData = $productLink->getData();

        $linkDataDiff = array_diff_assoc($linkToCompareData, $productLinkData);

        $linkDataDiff = array_intersect_key(
            $linkDataDiff,
            $productLinkData
        );

        return !empty($linkDataDiff);
    }

    /**
     * @param \Magento\Downloadable\Model\LinkRepository $subject
     * @param mixed $result
     * @param string $sku
     * @param Link $link
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\NotFoundException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Downloadable\Model\LinkRepository $subject,
        int $result,
        string $sku,
        LinkInterface $link
    ) {
        if ($this->canProcess()) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->get($sku, true);
            /** @var \Magento\Downloadable\Model\Product\Type $typeInstance */
            $typeInstance = $product->getTypeInstance();
            $productLinks = $typeInstance->getLinks($product);

            if ($link->getId() !== null) {
                $productLink = $productLinks[$link->getId()];
                if ($this->hasDataChanges($productLink, $link)) {
                    $this->linkOperationManager->syncLink($link, 'update');
                }
            } else {
                /** Load link by $result(ID). The one from parameters array hasn't that. */
                $link = $this->linkManager->getLink($result);

                if ($link) {
                    $this->linkOperationManager->syncLink($link, 'add');
                }
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Downloadable\Model\LinkRepository $subject
     * @param int $id
     *
     * @return void
     * @throws \Magento\Framework\Exception\NotFoundException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDelete(\Magento\Downloadable\Model\LinkRepository $subject, int $id): void
    {
        if ($this->canProcess()) {
            $link = $this->linkManager->getLink($id);

            if ($link) {
                $this->linkOperationManager->syncLink($link, 'delete');
            }
        }
    }

    private function canProcess(): bool
    {
        return ($this->config->isEnabled() && $this->config->syncLinksOnProductSave());
    }
}
