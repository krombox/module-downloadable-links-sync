<?php

namespace Krombox\DownloadableLinksSync\Model;

class Config extends AbstractConfig
{
    public const CONFIG_XML_PATH = 'krombox_downloadabalelinkssync';
    public const SYNC_LINKS_ON_PRODUCT_SAVE = 'general/sync_links_on_product_save';
    public const CHUNK_SIZE = 'general/chunk_size';

    public function syncLinksOnProductSave(): bool
    {
        return $this->isSetFlag(self::SYNC_LINKS_ON_PRODUCT_SAVE);
    }

    /**
     * @return int<1, max>
     */
    public function getChunkSize(): int
    {
        return $this->getValue(self::CHUNK_SIZE);
    }

    /**
     * @inheritDoc
     */
    public function getXmlPath(): string
    {
        return self::CONFIG_XML_PATH;
    }
}
