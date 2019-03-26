<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model;

use Magento\Framework\Exception\LocalizedException;

class Currency extends \Magento\Directory\Model\Currency
{

    /**
     * @var \Kozeta\Currency\Model\Precision
     */
    protected $precisionObject;

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
        $data = $this->_getResource()->getCurrencyRates($currency, $toCurrencies, $getUpdated_at);
        return $data;
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
        
        $data = $this->_getResource()->getCurrencyNames($currency);
        return $data;
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
            $options['precision'] = 2;
        }

        return $this->_localeCurrency->getCurrency($this->getCode())->toCurrency($price, $options);
    }
}
