<?php

namespace Krombox\DownloadableLinksSync\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

abstract class AbstractConfig
{
    public const ENABLED = 'general/enabled';

    /**
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected EncryptorInterface $encryptor
    ) {
    }

    /**
     * Check for enabled config
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isSetFlag(self::ENABLED);
    }

    /**
     * Get config value based on class CONFIG_XML_PATH
     *
     * @param string $field
     * @param int|null|string $storeId
     *
     * @return mixed
     */
    public function getValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $this->getXmlPath() . '/' . $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     *
     * @param string $path '{group}/{field}'
     * @param int|null|string $storeId
     *
     * @return bool
     */
    public function isSetFlag(
        $path,
        $storeId = null
    ) {
        return $this->getValue($path, $storeId);
    }

    /**
     * Get Xml Path for config class
     *
     * @return mixed
     */
    abstract public function getXmlPath();
}
