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

## Installation

To install, use composer:

```
composer require krombox/module-downloadable-links-sync
bin/magento module:enable Krombox_DownloadableLinksSync
bin/magento setup:upgrade
```

## Usage

To handle the queue either [CRON](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cli/configure-cron-jobs) or [CLI](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cli/start-message-queues) command needs to be run ``bin/magento queue:consumers:start krombox.downloadable_links.sync --max-messages=1``

## Prerequirements

1) PHP >= 8.0

## Donate

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/donate/?hosted_button_id=MWKEDP5DK5BMN)

## Credits

- [Roman Kapustian](https://github.com/krombox)


## License

The MIT License (MIT). Please see [License File](https://github.com/krombox/module-downloadable-links-sync/blob/master/LICENSE) for more information.
