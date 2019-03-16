Magento 2.0 Module "Any Currency"
====================


Overview
-----

The module is intended to fully replace the entire list of currencies and install custom currency list.


Objectives
-----
The main objective of the module is to give the ability to manage any currency regardless of whether it is available in Magento or not.

It will be especially useful for those who accept cryptocurrencies in their Magento 2 shops


Specifications
-----
The module will replace the currencies offered by Magento with your own currencies. The currencies can be managed the same way as usually. Some features are improved and extended. A few new features were added.


The existing currencies will be hidden once the module is installed and enabled. New currencies can be added and managed via Magento backend interface.

The common parameters management is improved and is available in Configuration menu.

Currency rates can be imported using the existing services once your currency codes are supported by those services.


Product features:
-----
<ul>
 - Price Precision
-----
 Now the user can define the desired price precision (accuracy) for each display currency - the number of decimal positions after decimal separator to display the prices in frontend and backend. 


The default price precision is 2. The precision can vary from 0 to 8. Please take into account that prices saved into Magento database always have the precision 4 as defined in Magento database schema. So the precision higher than 4 will be meaningful only for display purpose. Practically the highest precision of 6 will be required if you display prices in Bitcoin. For such cryptocurrency as Ethereum or Bitcoin cash price precision of 4 is quite sufficient.


The precision of 0 can be used for such currencies as Indian Rupee, Russian Ruble, etc.</span></li>

<p><span style="font-weight: 400;">The user can setup the price precision in website scope using Configuration -&gt; Catalog menu. </span><span style="font-weight: 400;"><br></span><span style="font-weight: 400;">There are three options to choose from: </span><span style="font-weight: 400;"><br></span> 1. Leave the default Magento price precision - 2.<span style="font-weight: 400;"><br></span> <span style="font-weight: 400;">2. Set a fixed price precision: 0, 2, 4, 6 or 8.</span><span style="font-weight: 400;"><br></span> 3. Automatically use price precision assigned for displayed currency.<span style="font-weight: 400;"><br></span>So, using the last option you can easily switch display currencies between Indian Rupee and Bitcoin within the same store.</p>

<li style="font-weight: 400;"><strong>Currency rate import services added</strong>. <em>Frankfurter</em> and <em>Coinapi</em> services were added. <span style="font-weight: 400;"><br></span><a href="https://frankfurter.app/">Frankfurter</a><span style="font-weight: 400;"> (alternative to Fixer.io) is completely free service. No API key or signup required. Frankfurter provides the rates of the most &nbsp;popular fiat currencies. Currency rates are being updated every working day around 4PM CET.</span><span style="font-weight: 400;"><br></span><a href="https://www.coinapi.io">Coinapi</a><span style="font-weight: 400;"> provides a few thousand currencies including all popular cryptocurrencies. Coinapi claims that all cryptocurrency exchanges are integrated under a single API. One needs to signup and get API key to import the rates. The service is provided for free for up to 100 requests per day. 100 requests per day is approximately 1 request every 15 minutes - that suffices for the majority of internet shop. If however someone needs more frequent updates Coinapi provides different packages from $80/mo to $600/mo for up to 100k requests per day.</span></li>
<li style="font-weight: 400;"><strong>Minute-wice rates import scheduling</strong>. You sure will need to update the rates of currencies at least a few times per hour in order to keep the prices current If cryptocurrencies are used in your shop due to cryptocurrencies volatility.<span style="font-weight: 400;"><br></span><span style="font-weight: 400;">Now it’s possible to schedule the import rates starting from once per minute using new minute-wice scheduler with crontab syntax.</span><span style="font-weight: 400;"><br></span>New Cron Configuration Options were added as well for currency rates import.</li>
<li style="font-weight: 400;"><strong>Currency Rates setup page</strong> was re-designed so that any number of currencies can be displayed in multiple rows. Number of currencies per row can be set in Configuration menu.</li>
<li style="font-weight: 400;"><strong>Display currency list on frontend</strong><span style="font-weight: 400;">. Optionally the user can display the list of currencies or brief info of any installed currency on frontend. The list can be linked from main menu and/or top menu. The user can assign an URL for the list. The content of the list can be inserted into CMS page or block, or into category description as a block.</span></li>
</ul>
 
Compatibility
-----
The module was tested with the following Magento versions: 2.1.x, 2.2.x, 2.3.x

It was not tested with Magento 2.0.x


<h2>Instructions</h2>

=======
<div class="value"><h2><strong>Overview</strong></h2>
<p>The module is intended to fully replace the entire list of currencies and install custom currency list.</p>
<h2><strong>Objectives</strong></h2>
<p>The main objective of the module is to give the ability to manage any currency regardless of whether it is available in Magento or not. </p>
<p>It will be especially useful for those who accept cryptocurrencies in their Magento 2 shops</p>
<h2><strong>Specifications</strong></h2>
<p>The module will replace the currencies offered by Magento with your own currencies. The currencies can be managed the same way as usually. Some features are improved and extended. A few new features were added.</p>
<p>The existing currencies will be hidden once the module is installed and enabled. New currencies can be added and managed via Magento backend interface. </p>
<p>The common parameters management is improved and is available in Configuration menu.</p>
<p>Currency rates can be imported using the existing services once your currency codes are supported by those services.</p>
<h2><span style="font-weight: 400;">Product features:</span></h2>
<ul>
<li style="font-weight: 400;"><strong>Price Precision</strong><span style="font-weight: 400;">. Now the user can define the desired price precision (accuracy) for each display currency - the number of decimal positions after decimal separator to display the prices in frontend and backend. </span><span style="font-weight: 400;"><br></span>The default price precision is 2. The precision can vary from 0 to 8. Please take into account that prices saved into Magento database always have the precision 4 as defined in Magento database schema. So the precision higher than 4 will be meaningful only for display purpose. Practically the highest precision of 6 will be required if you display prices in Bitcoin. For such cryptocurrency as Ethereum or Bitcoin cash price precision of 4 is quite sufficient.<span style="font-weight: 400;"><br></span>The precision of 0 can be used for such currencies as Indian Rupee, Russian Ruble, etc.</li>
</ul>
<p><span style="font-weight: 400;">The user can setup the price precision in website scope using Configuration -&gt; Catalog menu. </span><span style="font-weight: 400;"><br></span><span style="font-weight: 400;">There are three options to choose from: </span><span style="font-weight: 400;"><br></span> 1. Leave the default Magento price precision - 2.<span style="font-weight: 400;"><br></span> <span style="font-weight: 400;">2. Set a fixed price precision: 0, 2, 4, 6 or 8.</span><span style="font-weight: 400;"><br></span> 3. Automatically use price precision assigned for displayed currency.<span style="font-weight: 400;"><br></span>So, using the last option you can easily switch display currencies between Indian Rupee and Bitcoin within the same store.</p>
<ul>
<li style="font-weight: 400;"><strong>Currency rate import services added</strong>. <em>Frankfurter</em> and <em>Coinapi</em> services were added. <span style="font-weight: 400;"><br></span><a href="https://frankfurter.app/">Frankfurter</a><span style="font-weight: 400;"> (alternative to Fixer.io) is completely free service. No API key or signup required. Frankfurter provides the rates of the most &nbsp;popular fiat currencies. Currency rates are being updated every working day around 4PM CET.</span><span style="font-weight: 400;"><br></span><a href="https://www.coinapi.io">Coinapi</a><span style="font-weight: 400;"> provides a few thousand currencies including all popular cryptocurrencies. Coinapi claims that all cryptocurrency exchanges are integrated under a single API. One needs to signup and get API key to import the rates. The service is provided for free for up to 100 requests per day. 100 requests per day is approximately 1 request every 15 minutes - that suffices for the majority of internet shop. If however someone needs more frequent updates Coinapi provides different packages from $80/mo to $600/mo for up to 100k requests per day.</span></li>
<li style="font-weight: 400;"><strong>Minute-wice rates import scheduling</strong>. You sure will need to update the rates of currencies at least a few times per hour in order to keep the prices current If cryptocurrencies are used in your shop due to cryptocurrencies volatility.<span style="font-weight: 400;"><br></span><span style="font-weight: 400;">Now it’s possible to schedule the import rates starting from once per minute using new minute-wice scheduler with crontab syntax.</span><span style="font-weight: 400;"><br></span>New Cron Configuration Options were added as well for currency rates import.</li>
<li style="font-weight: 400;"><strong>Currency Rates setup page</strong> was re-designed so that any number of currencies can be displayed in multiple rows. Number of currencies per row can be set in Configuration menu.</li>
<li style="font-weight: 400;"><strong>Display currency list on frontend</strong><span style="font-weight: 400;">. Optionally the user can display the list of currencies or brief info of any installed currency on frontend. The list can be linked from main menu and/or top menu. The user can assign an URL for the list. The content of the list can be inserted into CMS page or block, or into category description as a block.</span></li>
</ul>
 
Compatibility
-----
The module was tested with the following Magento versions: 2.1.x, 2.2.x, 2.3.x

It was not tested with Magento 2.0.x


<h2>Instructions</h2>

 
Install
-----

Manually:
To install this module copy the code from this repo to `app/code/Kozeta/Currency` folder of your Magento 2 instance.

Via composer

 - composer config repositories.kozeta-module-currency git git@github.com:melaxon/Kozeta_Currency.git
 - composer require melaxon/kozeta-currency:dev-master

Run the following commands one by one:

- `$ php bin/magento setup:upgrade`
- `$ php bin/magento setup:di:compile`
- `$ php bin/magento setup:static-content:deploy`


Uninstall
--------

If you installed it manually:
 - remove the folder `app/code/Kozeta/Currency`
 - drop the tables `kozeta_currency_coin_store` and `kozeta_currency_coin` (in this order)
 - remove the config settings.  `DELETE FROM core_config_data WHERE path LIKE 'kozeta_currency/%'`
 - remove the module `Kozeta_Currency` from `app/etc/config.php`
 - remove the module `Kozeta_Currency` from table `setup_module`: `DELETE FROM setup_module WHERE module='Kozeta_Currency'`

If you installed it via composer:
 - run this in console  `bin/magento module:uninstall -r Kozeta_Currency`. You might have some problems while uninstalling. See more [details here](http://magento.stackexchange.com/q/123544/146)


In case you are not quite sure how to manage this or you have complicated configuration you can order professional installation service 


Settings
--------

Once the modules are installed all the existing currencies will disappear from the lists of installed and allowed currencies. So the first thing you need to do is to install currencies you need in your shop.

<h3>Currency installation</h3>
<p><span style="font-weight: 400;">To install and manage your currencies please proceed to Store -&gt; Manage currencies menu.</span></p>
<p><span style="font-weight: 400;"><img src="https://shop.kozeta.lt/pub/media/wysiwyg/2019-02-11_01-56-14.png" alt="Setup currencies menu interface" width="454" height="454"></span></p>
<p>&nbsp;</p>
<p><span style="font-weight: 400;">Then click “</span><strong>Add new currency</strong><span style="font-weight: 400;">” button and fill the form. The required fields are only Currency name and Code. All other fields are optional.</span><span style="font-weight: 400;"><br></span><span style="font-weight: 400;">If you set the currency as Inactive it won’t be shown up in Magento’s lists of installed and allowed currencies.</span><span style="font-weight: 400;"><br></span>If you switch <strong>RSS</strong> on this currency will appear in your RSS channel on frontend.</p>
<p><strong>Frontend</strong> section is used if you plan to display the currency list and currency info pages on frontend. You can setup meta data and URL key for currency info page. <span style="font-weight: 400;"><br></span>The <strong>avatar</strong> is also used in backend currency list.</p>

IMPORTANT

<table>
<tbody>
<tr>
<td>
<p>If you run multilingual site you can translate currency name and frontend info using i18n.</p>
<p>The module distribution contains a directory i18n with sample files for English and Russian languages. You can append your info into existing files or create similar files for desired languages</p>
</td>
</tr>
</tbody>
</table>
<p>&nbsp;</p>
<p><strong>Advanced</strong> section is intended for developers. in case if you install cryptocurrencies this will give you an interface to some cryptocurrency parameters.</p>
<p>&nbsp;</p>
<p><span style="font-weight: 400;">When you finish the installation of currencies the currency grid will look like this:</span></p>
<p><span style="font-weight: 400;"><img src="https://shop.kozeta.lt/pub/media/wysiwyg/Screenshot_2019-02-06_00.59.08.png" alt="backend currency list" width="658" height="351"></span></p>
<p>&nbsp;</p>
<p><span style="font-weight: 400;">Some parameters: Currency name, Symbol, Is active, Precision and Sort order can be edited directly in the grid.</span></p>
<p></p>
<p><span style="font-weight: 400;">Then you need to select the installed currencies as you do it in standard Magento installation. Please proceed to Configuration -&gt; Advanced -&gt; System -&gt; Currency and select the currencies you need from the list and click Save button.:</span></p>
<p>&nbsp;<img src="https://shop.kozeta.lt/pub/media/wysiwyg/2019-02-07_01-28-46.png" alt="select available currecies" width="612" height="325"></p>
<p>&nbsp;</p>
<p><span style="font-weight: 400;">The module creates new cron group: kozeta_currency that can be configured within the same section. </span></p>
<p><span style="font-weight: 400;"><img src="https://shop.kozeta.lt/pub/media/wysiwyg/2019-02-07_01-43-07.png" alt="New cron group" width="808" height="311"></span></p>
<p>&nbsp;</p>
<p>But we recommend to leave the default settings unless you have some special requirements. See more details on cron job and group settings in Magento <a href="https://devdocs.magento.com/guides/v2.3/config-guide/cron/custom-cron-ref.html">DevDocs</a></p>
<p><br>&nbsp;</p>
<h3>Setup Allowed currencies</h3>
<p>&nbsp;</p>
<p><span style="font-weight: 400;">The same way as you do it in standard Magento configuration, &nbsp;you will need to choose Allowed currencies, Base currency and Default display currency in Configuration menu:</span></p>
<p><span style="font-weight: 400;">Configuration -&gt; Default -&gt; Currency setup</span></p>
<p><span style="font-weight: 400;"><img src="https://shop.kozeta.lt/pub/media/wysiwyg/2019-02-07_02-01-40.png" alt="Allowed currencies setup" width="710" height="296"></span></p>
<p>&nbsp;</p>
<p><strong>Note</strong><span style="font-weight: 400;">: If cryptocurrencies are among the currencies in your shop you are discouraged to use them as default due to their high volatility nowadays.</span></p>
<p>&nbsp;</p>
<h3>Frontend display currency settings</h3>
<p>&nbsp;</p>
<p>If you want the list of installed currencies to be displayed on frontend you can use these display options.</p>
<p><span style="font-weight: 400;">Currency list is displayed on a separate page. This page link can be placed on either or both Top Menu and/or Top Links menu.</span></p>
<p><strong>Top menu title</strong> will be displayed in front of category links on Top Menu or disable if this field is not filled in.</p>
<p>&nbsp;<img src="https://shop.kozeta.lt/pub/media/wysiwyg/Screenshot_2019-02-09_02.03.17.png" alt="top menu" width="622" height="544"></p>
<p>&nbsp;</p>
<p><strong>Top links title</strong> of the currency list link will be displayed after default welcome message if you fill in this field.</p>
<p><img src="https://shop.kozeta.lt/pub/media/wysiwyg/2019-02-09_02-12-42.png" alt="top menu title" width="727" height="153"></p>
<p>&nbsp;</p>
<p><span style="font-weight: 400;"><img style="float: right;" src="https://shop.kozeta.lt/pub/media/wysiwyg/2019-02-09_02-18-42.png" alt="currency frontend info page" width="453" height="644"></span></p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><strong>Enable currency description pages</strong><span style="font-weight: 400;"> - if set to Yes then description page for each currency will be enabled and shown like on the screenshot:</span></p>
<p style="clear: both;"><span style="font-weight: 400;">&nbsp;</span></p>
<p><span style="font-weight: 400;">&nbsp;</span></p>
<p><br><br><br></p>
<h3>&nbsp;</h3>
<h3>Currency Rate Settings</h3>
<p><br><img src="https://shop.kozeta.lt/pub/media/wysiwyg/2019-02-12_01-36-32.png" alt="Currency Rate Settings" width="644" height="177"></p>
<p>&nbsp;</p>
<p><span style="font-weight: 400;">Currency Rate Settings group contains one parameter: </span><strong>Number of currencies per row</strong><span style="font-weight: 400;">. &nbsp;Its value defines the number of currencies displayed in one row in Currency Rate page. It is useful only if you have large number of allowed currencies that will not fit in single row as it happens in standard Magento configuration. E.g. if you have eight currencies you can split them into 2 rows. This will look like this:</span></p>
<p><img src="https://shop.kozeta.lt/pub/media/wysiwyg/Screenshot_2019-02-09_15.19.12.jpg" alt="currency rates multirow page" width="773" height="342"></p>
<p>&nbsp;</p>
<p>Please note that this value can be modified in Configuration interface in Global scope only.</p>
<p>A currency will be shown on this page if it is set as Active and selected in the Allowed Currency List.</p>
<p>In order to import the rate of a given currencies please make sure that the selected service supports those currency codes.</p>
<p>&nbsp;</p>
<p><strong>Scheduled Import Settings</strong><span style="font-weight: 400;"> - some new features were added to this settings group:</span></p>
<p><span style="font-weight: 400;">If disabled it will hide all other parameters:</span></p>
<p><span style="font-weight: 400;"><img src="https://shop.kozeta.lt/pub/media/wysiwyg/Screenshot_2019-02-09_15.48.41.png" alt="Scheduled Import Settings" width="785" height="168"></span></p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><strong>Enabled Minute-wice scheduling</strong> - if enabled it will allow you to schedule the currency rate import frequency starting from once per minute. It will hide the standard Magento scheduler and show its own <strong>Schedule</strong><span style="font-weight: 400;"> parameter:</span></p>
<p><span style="font-weight: 400;"><img src="https://shop.kozeta.lt/pub/media/wysiwyg/minute-wise-scheduler.png" alt="minute-wise settings" width="667" height="353"></span></p>
<p>&nbsp;</p>
<p><span style="font-weight: 400;">This utilizes the crontab scheduling definitions to schedule the import. For more details please check this article: </span><a href="https://en.wikipedia.org/wiki/Cron"><span style="font-weight: 400;">https://en.wikipedia.org/wiki/Cron</span></a></p>
<p>For example, as it is shown on the screenshot the import will run every 15 minutes.</p>
<p>All Scheduled Import Settings parameters now are shown in Global scope only, except for Error Email Recipient that remains in Store scope and Error Email Sender and Error Email Template remain in Website scope. </p></div>

