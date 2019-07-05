<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */
declare(strict_types=1);

namespace Kozeta\Currency\Model\Currency\Import;

use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Directory\Model\Currency\Import\AbstractImport;

class CurrencyConverterApi extends AbstractImport
{
    /**
     * @var string
     */
    const CURRENCY_CONVERTER_URL = 'https://free.currconv.com/api/v7/convert?q={{CURRENCY_PAIRS}}&compact=ultra&apiKey={{API_KEY}}'; //@codingStandardsIgnoreLine

    /**
     * Http Client Factory
     *
     * @var ZendClientFactory
     */
    private $httpClientFactory;

    /**
     * Core scope config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Initialize dependencies
     *
     * @param CurrencyFactory $currencyFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ZendClientFactory $httpClientFactory
     */
    public function __construct(
        CurrencyFactory $currencyFactory,
        ScopeConfigInterface $scopeConfig,
        ZendClientFactory $httpClientFactory
    ) {
        parent::__construct($currencyFactory);
        $this->scopeConfig = $scopeConfig;
        $this->httpClientFactory = $httpClientFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchRates()
    {
        $data = [];
        $currencies = $this->_getCurrencyCodes();
        $defaultCurrencies = $this->_getDefaultCurrencyCodes();

        foreach ($defaultCurrencies as $currencyFrom) {
            if (!isset($data[$currencyFrom])) {
                $data[$currencyFrom] = [];
            }
            $data = $this->convertBatch($data, $currencyFrom, $currencies);
            ksort($data[$currencyFrom]);
        }
        return $data;
    }

    /**
     * Return currencies convert rates in batch mode
     * Api key added
     *
     * @param array $data
     * @param string $currencyFrom
     * @param array $currenciesTo
     * @return array
     */
    private function convertBatch($data, $currencyFrom, $currenciesTo)
    {
        $pairs = [];
        foreach ($currenciesTo as $to) {
            $pairs[] = $currencyFrom . '_' . $to;
        }
        set_time_limit(0);
        $apiKey = $this->scopeConfig->getValue('currency/currencyconverterapi/api_key', ScopeInterface::SCOPE_STORE);
        $currencyPairs = implode(",", $pairs);
        $url = str_replace('{{CURRENCY_PAIRS}}', $currencyPairs, self::CURRENCY_CONVERTER_URL);
        $url = str_replace('{{API_KEY}}', $apiKey, $url);

        try {
            $response = $this->getServiceResponse($url);
            if (empty($response)) {
                $this->_messages[] = __('Service CurrencyConverterApi returns no data. Please check settings', $url, $to);
                $data[$currencyFrom] = [];
                return $data;
            }
            if (isset($response['error']) ) {
                $this->_messages[] = __('Error message from Service CurrencyConverterApi: %1', $response['error']);
                $data[$currencyFrom] = [];
                return $data;
            }
            foreach ($currenciesTo as $to) {
                $pair = $currencyFrom . '_' . $to;
                if (!isset($response[$pair]) || empty($response[$pair])) {
                    $this->_messages[] = __('CurrencyConverterApi does not return rates for %1.', $to);
                    $data[$currencyFrom][$to] = null;
                    continue;
                }
                $data[$currencyFrom][$to] = $this->_numberFormat(
                    (double)$response[$currencyFrom . '_' . $to]
                );
            }
        } finally {
            ini_restore('max_execution_time');
        }

        return $data;
    }

    /**
     * Get Currency Converter API service response
     *
     * @param string $url
     * @param int $retry
     * @return array
     */
    private function getServiceResponse($url, $retry = 0)
    {
        /** @var ZendClientFactory $httpClient */
        $httpClient = $this->httpClientFactory->create();
        $response = [];

        try {
            $jsonResponse = $httpClient->setUri(
                $url
            )->setConfig(
                [
                    'timeout' => $this->scopeConfig->getValue(
                        'currency/currencyconverterapi/timeout',
                        ScopeInterface::SCOPE_STORE
                    ),
                ]
            )->request(
                'GET'
            )->getBody();

            $response = json_decode($jsonResponse, true);
        } catch (\Exception $e) {
            if ($retry == 0) {
                $response = $this->getServiceResponse($url, 1);
            }
        }
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function _convert($currencyFrom, $currencyTo)
    {
        return 1;
    }
}
