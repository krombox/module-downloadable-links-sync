<?php

namespace Krombox\DownloadableLinksSync\Plugin\Model;

use Krombox\DownloadableLinksSync\Model\Config;
use Krombox\DownloadableLinksSync\Model\Link\Handler;
use Krombox\DownloadableLinksSync\Model\Link\Handler\Add;
use Krombox\DownloadableLinksSync\Model\Link\Handler\Delete;
use Krombox\DownloadableLinksSync\Model\Link\Handler\Update;
use Krombox\DownloadableLinksSync\Model\Link\Manager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Downloadable\Model\Link;

class LinkRepository
{
    public function __construct(
        private Handler $linkHandler,
        private ProductRepositoryInterface $productRepository,
        private Manager $linkManager,
        private Config $config
    ) {
    }

    private function hasDataChanges(Link $productLink, Link $linkToCompare): bool
    {
        /** Set price precision to 6 digits to align with data from DB */
        $linkToCompare->setPrice((float)number_format($linkToCompare->getPrice(), 6));
        $linkToCompareData = $linkToCompare->toArray();
        $productLinkData = $productLink->toArray();
        $linkDataDiff = array_intersect_key(array_diff_assoc($linkToCompareData, $productLinkData), $productLinkData);
        return !empty($linkDataDiff);
    }

    /**
     * @param \Magento\Downloadable\Model\LinkRepository $subject
     * @param mixed $result
     * @param string $sku
     * @param Link $link
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\NotFoundException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(\Magento\Downloadable\Model\LinkRepository $subject, mixed $result, string $sku, Link $link): void
    {
        if ($this->canProcess()) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->get($sku, true);
            /** @var \Magento\Downloadable\Model\Product\Type $typeInstance */
            $typeInstance = $product->getTypeInstance();
            $productLinks = $typeInstance->getLinks($product);

            if ($link->getId() !== null) {
                $productLink = $productLinks[$link->getId()];
                if ($this->hasDataChanges($productLink, $link)) {
                    $this->linkHandler->execute($link, Update::ACTION_NAME);
                }
            } else {
                /** Load link by $result(ID). The one from parameters array hasn't that. */
                $link = $this->linkManager->getLink($result);

                if($link) {
                    $this->linkHandler->execute($link, Add::ACTION_NAME);
                }
            }
        }
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
                $this->linkHandler->execute($link, Delete::ACTION_NAME);
            }
        }
    }

    private function canProcess(): bool
    {
        return ($this->config->isEnabled() && $this->config->syncLinksOnProductSave());
    }
}
