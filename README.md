Magento 2.0 Module "Any Currency"
====================

Current version: 1.0.3 - bugfix: if USD is not base currency some services fail to import rates - fixed

1.0.2 - initial release


Full description is available here: https://shop.kozeta.lt/m2-any-currency.html


Overview
-----

The module is intended to fully replace the entire list of currencies and install custom currency list.


Objectives
-----
The main objective of the module is to give the ability to manage any currency regardless of whether it is available in Magento or not.

It will be especially useful for those who accept cryptocurrencies in their Magento 2 shops.

Another objective is to give the ability to display prices in alternative currencies equal to the amounts the customer will be billed.
For that purpose we added some new rate import services provided by payment processors: coinpayments.net, BitPay, CoinGate. 
So if these processors are installed it will be possible to display exactly same prices as will be billed during checkout.

 
Install
-----

Manually:
To install this module copy the code from this repo to `app/code/Kozeta/Currency` folder of your Magento 2 instance.


Run the following commands one by one:

- `$ php bin/magento setup:upgrade`
- `$ php bin/magento setup:di:compile`
- `$ php bin/magento setup:static-content:deploy`


Uninstall
--------

 - remove the folder `app/code/Kozeta/Currency`
 - drop the tables `kozeta_currency_coin_store`, `kozeta_currency_currency_rate` and `kozeta_currency_coin` (in this order)
 - remove the config settings.  `DELETE FROM core_config_data WHERE path LIKE '%kozeta_currency%'`
 - remove the module `Kozeta_Currency` from `app/etc/config.php`
 - remove the module `Kozeta_Currency` from table `setup_module`: `DELETE FROM setup_module WHERE module='Kozeta_Currency'`

