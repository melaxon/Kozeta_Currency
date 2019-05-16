<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model\ResourceModel;

class Currency extends \Magento\Directory\Model\ResourceModel\Currency
{

    /**
     * Currency params
     *
     * @var array
     */
    private $_currencyData;
    
    /**
     * Substitute core table directory_currency_rate
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('directory_currency', 'currency_code');
        $this->_currencyRateTable = $this->getTable('kozeta_currency_currency_rate');
    }

    /**
     * Return currency rates
     *
     * @param string|array $currency
     * @param array $toCurrencies
     * @return array
     */
    public function getCurrencyRates($currency, $toCurrencies = null, $updated = null)
    {
        $rates = [];
        if (is_array($currency)) {
            foreach ($currency as $code) {
                $rates[$code] = $this->_getRatesByCode($code, $toCurrencies);
            }
        } else {
            $rates = $this->_getRatesByCode($currency, $toCurrencies, $updated);
        }

        return $rates;
    }

    /**
     * Return currency rates and time
     *
     * @param string|array $currency
     * @param array $toCurrencies
     * @return array
     */
    public function getCurrencyRatesUpdated($currency, $toCurrencies = null)
    {
        $rates = [];
        if (is_array($currency)) {
            foreach ($currency as $code) {
                $rates[$code] = $this->_getRatesByCode($code, $toCurrencies, 1);
            }
        } else {
            $rates = $this->_getRatesByCode($currency, $toCurrencies, 1);
        }

        return $rates;
    }


    /**
     * Protected method used by getCurrencyRates() method
     *
     * @param string $code
     * @param array $toCurrencies
     * @return array
     * @SuppressWarnings(PHPMD.Ecg.Sql.SlowQuery)
     */
    protected function _getRatesByCode($code, $toCurrencies = null, $updated = null)
    {
        $fieldsList = ['currency_to', 'rate'];
        if ($updated !== null) {
            $fieldsList[] = 'updated_at';
            $fieldsList[] = 'currency_converter_id';
        }
        $connection = $this->getConnection();
        $bind = [':currency_from' => $code];
        $select = $connection->select()->from(
            $this->_currencyRateTable,
            $fieldsList
        )->where(
            'currency_from = :currency_from'
        )->where(
            'currency_to IN(?)',
            $toCurrencies
        );
        // @codingStandardsIgnoreStart
        $rowSet = $connection->fetchAll($select, $bind);
        // @codingStandardsIgnoreEnd
        $result = [];
        
        if ($updated !== null) {
            foreach ($rowSet as $row) {
                $result[$row['currency_to']]['rate'] = $row['rate'];
                $result[$row['currency_to']]['updated_at'] = $row['updated_at'];
                $result[$row['currency_to']]['currency_converter_id'] = $row['currency_converter_id'];
            }
        } else {
            foreach ($rowSet as $row) {
                $result[$row['currency_to']] = $row['rate'];
            }
        }

        return $result;
    }
    
    /**
     * Return currency names
     *
     * @param string|array $code
     * @return array
     * @SuppressWarnings(PHPMD.Ecg.Sql.SlowQuery)
     */
    public function getCurrencyNames($code)
    {
        return $this->getCurrencyParamByCode($code, 'name');
    }
    
    /**
     * Return currency symbol
     *
     * @param string|array $code
     * @return array
     * @SuppressWarnings(PHPMD.Ecg.Sql.SlowQuery)
     */
    public function getCurrencySymbol($code) 
    {
        return $this->getCurrencyParamByCode($code, 'symbol');
    }
    
    /**
     * Return currency precision
     *
     * @param string|array $code
     * @return array
     * @SuppressWarnings(PHPMD.Ecg.Sql.SlowQuery)
     */
    public function getCurrencyPrecision($code) 
    {
        return $this->getCurrencyParamByCode($code, 'precision');
    }
    
    /**
     * Return currency parameters
     *
     * @param string|array $code
     * @param string|array $param
     * @return array
     * @SuppressWarnings(PHPMD.Ecg.Sql.SlowQuery)
     */
    public function getCurrencyParamByCode($code, $param = null)
    {
        $result = [];
        $_code = [];
        if (!is_array($code)) {
            $code = [$code];
        }
        $_code = $code;
        foreach ($code as $k => $c) {
            if (isset($this->_currencyData[$c])) {
                unset($code[$k]);
                continue;
            }
        }

        if (isset($this->_currencyData)) {
            foreach ($this->_currencyData as $row) {
                $result[$row['code']] = $param ? $row[$param] : $row;
            }
        }

        if ($code){
            $connection = $this->getConnection();
            $select = $connection->select()->from(
                $this->getTable('kozeta_currency_coin'),
                ['*']
            )->where(
                'code IN(?)',
                $code
            );
            // @codingStandardsIgnoreStart
            $rowSet = $connection->fetchAll($select);
            // @codingStandardsIgnoreEnd

            foreach ($rowSet as $row) {
                $result[$row['code']] = $param ? $row[$param] : $row;
                $this->_currencyData[$row['code']] = $row;
            }
        }

        $_result = [];
        foreach ($_code as $k => $c) {
            if (isset($result[$c])) {
                $_result[$c] = $result[$c];
            }
        }

        return $_result;
    }

    /**
     * Saving currency rates
     *
     * @param array $rates
     * @param array $service optional
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveRates($rates, $service = null)
    {
        $manual = false;
        if (!$service) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->appState = $objectManager->get('Magento\Framework\App\State');
            if ($this->appState->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
                $manual = true;
            }
        }

        if (is_array($rates) && sizeof($rates) > 0) {
            $connection = $this->getConnection();
            $data = [];
            foreach ($rates as $currencyCode => $rate) {
                $_fields = [];
                foreach ($rate as $currencyTo => $value) {
                    $value = abs($value);
                    if ($value == 0) {
                        continue;
                    }
                    $_fields['currency_from'] = $currencyCode;
                    $_fields['currency_to'] = $currencyTo;
                    $_fields['rate'] = $value;
                    if (is_array($service)) {
                        $_fields['currency_converter_id'] = $service[$currencyCode][$currencyTo] ?: '_';
                    } elseif ($manual) {
                        $_fields['currency_converter_id'] = 'manually';
                    }
                    $data[] = $_fields;
                }
            }
            if ($data) {
                $connection->insertOnDuplicate($this->_currencyRateTable, $data, ['rate','currency_converter_id']);
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please correct the rates received'));
        }
    }
}
