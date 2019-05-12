<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model;

use Magento\Framework\Exception\LocalizedException;

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
     * @var \Kozeta\Currency\Model\Currency
     */
    private $runtimeCurrencies;

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

        throw new \Exception(__(
            'Undefined rate from "%1-%2".',
            $this->getCode(),
            $this->getCurrencyCodeFromToCurrency($toCurrency)
        ));
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
        $options['symbol'] = $data[$this->getCode()] ?: $this->getCode();
        //$options['position'] = 16;

        return $this->_localeCurrency->getCurrency($this->getCode())->toCurrency($price, $options);
    }

    /**
     * Retrieve current currency set if exists
     *
     * @return array
     */
    public function getConfigAllowCurrencies()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->currencyObject = $objectManager->get('Kozeta\Currency\Model\Schedule');
        $allowedCurrencies = $this->currencyObject->getCurrencies();
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

        return parent::getConfigAllowCurrencies();
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
