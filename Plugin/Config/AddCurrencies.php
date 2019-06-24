<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Plugin\Config;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Kozeta\Currency\Model\Coin;
use Kozeta\Currency\Model\ResourceModel\Coin\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Directory\Model\CurrencyFactory;

class AddCurrencies
{
    /**
     * @var AddCurrencies
     */
     private $codes;
     
    /**
     * @var CurrencyFactory
     */
     private $currencyFactory;

    /**
     * @var CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    
    /**
     * Installed currencies
     */
    const XML_PATH_CURRENCY_INSTALLED = 'system/currency/installed';
    
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    
    /**
     * New currencies list
     *
     * @var array
     */
    private $coins;
    
    /**
     *
     * @var Http
     */
    private $request;

    /**
     * @param CurrencyFactory $currencyFactory
     * @param CurrencyInterface $localeCurrency
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface $config
     * @param ResolverInterface $localeResolver
     * @param CollectionFactory $collectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param Http $request
     * @param $locale
     */
    public function __construct(
        CurrencyFactory $currencyFactory,
        CurrencyInterface $localeCurrency,
        StoreManagerInterface $storeManager,
        ConfigInterface $config,
        ResolverInterface $localeResolver,
        CollectionFactory $collectionFactory,
        ScopeConfigInterface $scopeConfig,
        Http $request,
        $locale = null
    ) {
        $this->currencyFactory = $currencyFactory;
        $this->localeCurrency = $localeCurrency;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->localeResolver = $localeResolver;
        if ($locale !== null) {
            $this->localeResolver->setLocale($locale);
        }
        $this->collectionFactory = $collectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
    }

    /**
     * Retrieve installed currency list
     *
     * @return array
     */
    private function getNewCurrencies()
    {
        if ($this->coins) {
            return $this->coins;
        }
        $store_id = (int) $this->request->getParam('store', 0);
        $currencies = [];
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect('*');
        $collection->addFieldToFilter('is_active', Coin::STATUS_ENABLED);
        $collection->setOrder('name', 'ASC');
        $collection->addStoreFilter($store_id);
        foreach ($collection as $_coin) {
            $_coin->getIsActive() == false ?: $currencies[] = [
                'value' => $_coin->getCode(),
                'label' => __($_coin->getName())
            ];
        }
        $this->coins = $this->sortOptionArray($currencies);
        return $this->coins;
    }

    /**
     * Sort and restruct array
     *
     * @param array $option
     * @return array
     */
    private function sortOptionArray($option)
    {
        $data = [];
        foreach ($option as $item) {
            $data[$item['value']] = $item['label'];
        }
        asort($data);
        $option = [];
        foreach ($data as $key => $label) {
            $option[] = ['value' => $key, 'label' => $label];
        }
        return $option;
    }

    /**
     * Get currency name
     *
     * @param string $code
     * @return string
     */
    private function getCurrencyNameByCode($code)
    {
        $currencyManager = $this->currencyFactory->create();
        
        $name = $this->localeCurrency->getCurrency($code)->getName();
        if ($name == 'US Dollar' && $code != 'USD') {
            $name = null;
        }
        if ($name) {
            return $name;
        }
        $name = $currencyManager->getCurrencyNames($code);
        if (is_array($name)) {
            return isset($name[$code]) ? $name[$code] : $code;
        }
        return $name;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundGetOptionCurrencies($subject, \Closure $proceed, ...$args)
    {
        $installedCurrencies = $this->getNewCurrencies();
        $availableCurrencies = $this->storeManager->getStore()->getAvailableCurrencyCodes();

        if (empty($installedCurrencies)) {
            $locale = $this->localeResolver->getLocale();
            $currencies = (new \Magento\Framework\Locale\Bundle\CurrencyBundle())->get($this->localeResolver->getLocale())['Currencies'] ?: [];
            $options = [];

            foreach ($currencies as $code => $data) {
                if (!in_array($code, $availableCurrencies)) {
                    continue;
                }
                $options[] = ['label' => $data[1], 'value' => $code];
            }
            return $this->sortOptionArray($options);
        }
        $selectedCurrencies = explode(
            ',',
            $this->scopeConfig->getValue(
                'system/currency/installed',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        foreach ($installedCurrencies as $k => $c) {
            if (!in_array($c['value'], $selectedCurrencies)) {
                unset($installedCurrencies[$k]);
            }
        }
        
        $currencies = [];
        foreach ($availableCurrencies as $code) {
            if (isset($this->codes[$code])) {
                continue;
            }
            $label = $this->getCurrencyNameByCode($code);
            $currencies[] = [
                'value' => $code,
                'label' => $label,
            ];
            $this->codes[$code] = $label;
        }
        
        foreach ($installedCurrencies as $v) {
            if (isset($this->codes[$v['value']])) {
                continue;
            }
            $this->codes[$v['value']] = $v['label'];
            $currencies[] = $v;
        }

        return $currencies;
    }

    /**
     * @inheritdoc
     */
    public function aroundGetOptionAllCurrencies($subject, \Closure $proceed, ...$args)
    {
        return $this->getNewCurrencies() ?: $proceed();
    }
}
