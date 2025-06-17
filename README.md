# Magento 2 Downloadable Links Sync

[![Latest Version](https://img.shields.io/github/tag/krombox/module-downloadable-links-sync.svg?style=flat-square)](https://github.com/krombox/module-downloadable-links-sync/tags)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/krombox/module-downloadable-links-sync.svg?style=flat-square)](https://packagist.org/packages/krombox/module-downloadable-links-sync)

This module lets you sync new, edited, and deleted product downloadable links to existing orders.
Example: Your store has product A with a downloadable link, "link #1." There are several orders containing that product.
After some time you decided to add "link #2" and change the title for the initial link to "link #1 extended".
By clicking **Sync links** button on the product edit page or by saving the product(depending on settings) your links will be
synchronized with the existing orders.

The module uses [Magento`s message queue](https://developer.adobe.com/commerce/php/development/components/message-queues/) to process the links sync. AMQP(RabbitMQ) or DB connection is used depend on configuration.

## Prerequirements

1) PHP >= 8.0

## Installation

To install, use composer:

```
composer require krombox/module-downloadable-links-sync
bin/magento module:enable Krombox_DownloadableLinksSync
bin/magento setup:upgrade
```

## Usage

To process the queue, either enable [CRON](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cli/configure-cron-jobs) or run the following  [CLI](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cli/start-message-queues) command manually: ``bin/magento queue:consumers:start krombox.downloadable_links.sync --max-messages=1``

## CLI command

If you want to sync multiple products at once or are experiencing timeout issues due to a large number of related orders, you can use the CLI command as an alternative. By default, all products on your store will be processed.

``bin/magento krombox:downloadable_links:sync``

To sync specific product links, use the ``--product-ids`` option to limit processing to particular product IDs or ID ranges. In the example below, command processes products with the following IDs: 1,3,4,5,8,10,11,12.

``bin/magento krombox:downloadable_links:sync --product-ids 1,3-5,8,10-12``

## Extensibility

You can extend the module by creating a custom operation. To do so, create a [virtualType](https://developer.adobe.com/commerce/php/development/build/dependency-injection-file/#virtual-types) for the class ``Krombox\DownloadableLinksSync\Model\Link\Operation`` with the appropriate configuration.

```xml
<virtualType name="Krombox\DownloadableLinksSync\Model\Link\Operation\Custom" type="Krombox\DownloadableLinksSync\Model\Link\Operation">
    <arguments>
        <argument name="name" xsi:type="string">add</argument>
        <argument name="resolver" xsi:type="object">Krombox\DownloadableLinksSync\Model\Link\Resolver\Custom</argument>
        <argument name="processor" xsi:type="object">Krombox\DownloadableLinksSync\Model\Link\Processor\Custom</argument>
        <argument name="linkProvider" xsi:type="object">Krombox\DownloadableLinksSync\Model\Link\Provider\Main</argument>
    </arguments>
</virtualType>
```
 
Afterward, add custom operation to ``Krombox\DownloadableLinksSync\Model\Link\OperationPool``.

```xml
 <type name="Krombox\DownloadableLinksSync\Model\Link\OperationPool">
    <arguments>
        <argument name="operations" xsi:type="array">
            ...
            <item name="custom" xsi:type="object">Krombox\DownloadableLinksSync\Model\Link\Operation\Custom</item>
        </argument>
    </arguments>
</type>
```
    
Alternatively, you can create a custom operation class by implementing ``Krombox\DownloadableLinksSync\Model\Link\OperationInterface`` interface.
For more details, please refer to the ``etc/di.xml`` file.

## Donate

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/donate/?hosted_button_id=MWKEDP5DK5BMN)

**BTC (BitCoin)**: ``bc1qqae0g74a6a7djy9a2d76ns4r0ydqvkvm96u82q``

**LTC (Litecoin)**: ``ltc1qsq7l5dje5tlw3778z9vstm4vnf7y40fzczdc2q``

**ETH (Ethereum ERC20)**: ``0xADcA955BFd4b9080AfAD99143BF011369d92d7d6``

**SOL (Solana)**: ``H65224SYDw2v2BFVBDJQLAoVXgbwVdhKKyZ9zi6uKUe1``

**USDT (TRON TRC20)**: ``TXaWfYLG1mx82Wh2Ffn4ueze46cBi3jucn``



## Credits

- [Roman Kapustian](https://github.com/krombox)


## License

The MIT License (MIT). Please see [License File](https://github.com/krombox/module-downloadable-links-sync/blob/master/LICENSE) for more information.
