<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ObjectManager;

/*
 * Model Currency
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Currency extends \Magento\Directory\Model\Currency
{

    /**
     * @var \Kozeta\Currency\Model\Precision
     */
    private $precisionObject;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;
    
    /**
     * Retrieve currency rates to other currencies
     *
     * @param string $currency
     * @param array|null $toCurrencies
     * @return array
     */
    public function getCurrencyRates($currency, $toCurrencies = null, $getUpdated_at = null)
    {
        if ($currency instanceof \Magento\Directory\Model\Currency) {
            $currency = $currency->getCode();
        }

        if ($getUpdated_at) {
            return $this->_getResource()->getCurrencyRatesUpdated($currency, $toCurrencies);
        }

        return $this->_getResource()->getCurrencyRates($currency, $toCurrencies);
    }

    /**
     * Retrieve currency name
     * @param string $currency or object $currency
     * @return array name[code]
     */
    public function getCurrencyNames($currency)
    {
        if ($currency instanceof \Magento\Directory\Model\Currency) {
            $currency = $currency->getCode();
        }

        return $this->_getResource()->getCurrencyNames($currency);
    }

    /**
     * Return currency parameters
     * Return all parameters if no $param given
     * @param string|array $currency
     * @param string|array $param
     * @return array
     */
    public function getCurrencyParamByCode($currency, $param = null)
    {
        if ($currency instanceof \Magento\Directory\Model\Currency) {
            $currency = $currency->getCode();
        }
        return $this->_getResource()->getCurrencyParamByCode($currency, $param);
    }

    /**
     * Price precision
     * @param   float $price
     * @param   mixed $toCurrency
     * @return  float
     * @throws \Exception
     */
    public function convert($price, $toCurrency = null)
    {
        if ($toCurrency === null) {
            return $price;
        } elseif ($this->getCode() == $this->getCurrencyCodeFromToCurrency($toCurrency)) {
            return $price;
        } elseif ($rate = $this->getRate($toCurrency)) {
            return (float)$price * (float)$rate;
        }
        parent::convert($price, $toCurrency);
    }

    /**
     * Price precision
     * @param float $price
     * @param array $options
     * @return string
     */
    public function formatTxt($price, $options = [])
    {
        if (!($this->precisionObject instanceof \Kozeta\Currency\Model\Precision )) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->precisionObject = $objectManager->get('Kozeta\Currency\Model\Precision');
        }

        if (!is_numeric($price)) {
            $price = $this->_localeFormat->getNumber($price);
        }

        $price = sprintf("%F", $price);
        $precision = (int) $this->precisionObject->getPrecisionByCode($this->getCode());
        if (!empty($precision) || $precision === 0) {
            $options['precision'] = $precision;
        }
        if (empty($options['precision']) && $options['precision'] !== 0) {
            $options['precision'] = \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION;
        }

        $data = $this->_getResource()->getCurrencySymbol($this->getCode());
        $options['symbol'] = isset($data[$this->getCode()]) ? $data[$this->getCode()] : $this->getCode();
        //$options['position'] = 16;

        return $this->_localeCurrency->getCurrency($this->getCode())->toCurrency($price, $options);
    }

    /**
     * Get ScopeConfigInterface instance
     * @return mixed
     */
    private function getConfig()
    {
        $this->config = $this->config ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
        return $this->config;
    }
    /**
     * Retrieve current currency set if exists
     *
     * @return array
     */
    public function getConfigAllowCurrencies()
    {
        $allowedCurrencies = [];
        try {
            $runtimeCurrencies = \Kozeta\Currency\Model\Currency\RuntimeCurrencies::getInstance();
            $allowedCurrencies = $runtimeCurrencies->getImportCurrencies();
        } catch (\Exception $e) {
            return $this->getConfigCurrencies(parent::XML_PATH_CURRENCY_ALLOW);
        }

        if (is_array($allowedCurrencies)) {
            $appBaseCurrencyCode = $this->_directoryHelper->getBaseCurrencyCode();
            if (!in_array($appBaseCurrencyCode, $allowedCurrencies)) {
                $allowedCurrencies[] = $appBaseCurrencyCode;
            }
            foreach ($this->_storeManager->getStores() as $store) {
                $code = $store->getBaseCurrencyCode();
                if (!in_array($code, $allowedCurrencies)) {
                    $allowedCurrencies[] = $code;
                }
            }
            return $allowedCurrencies;
        }
        return $this->getConfigCurrencies(parent::XML_PATH_CURRENCY_ALLOW);
    }

    /**
     * Retrieve default currencies according to config
     *
     * @return array
     */
    public function getConfigDefaultCurrencies()
    {
        return $this->getConfigCurrencies(parent::XML_PATH_CURRENCY_DEFAULT);
    }

    /**
     * @return array
     */
    public function getConfigBaseCurrencies()
    {
        return $this->getConfigCurrencies(parent::XML_PATH_CURRENCY_BASE);
    }

    /**
     * Retrieve allowed, base or default currency data.
     *
     * @param string $path
     * @return array
     */
    public function getConfigCurrencies($path)
    {
        $result = in_array($this->_getResource()->getAreaCode(), [Area::AREA_ADMINHTML, Area::AREA_CRONTAB])
            ? $this->getConfigForAllStores($path)
            : $this->getConfigForCurrentStore($path);
        sort($result);

        return array_unique($result);
    }

    /**
     * Get allowed, base and default currency codes for all stores.
     *
     * @param string $path
     * @return array
     */
    private function getConfigForAllStores($path)
    {
        $storesResult = [[]];
        foreach ($this->_storeManager->getStores() as $store) {
            $storesResult[] = explode(
                ',',
                $this->getConfig()->getValue($path, ScopeInterface::SCOPE_STORE, $store->getCode())
            );
        }

        return array_merge(...$storesResult);
    }

    /**
     * Get allowed, base and default currency codes for current store.
     *
     * @param string $path
     * @return mixed
     */
    private function getConfigForCurrentStore($path)
    {
        $store = $this->_storeManager->getStore();

        return explode(',', $this->getConfig()->getValue($path, ScopeInterface::SCOPE_STORE, $store->getCode()));
    }

    /**
     * Save currency rates
     *
     * @param array $rates
     * @param array $service optional
     * @return $this
     */
    public function saveRates($rates, $service = null)
    {
        $this->_getResource()->saveRates($rates, $service);
        return $this;
    }
}
