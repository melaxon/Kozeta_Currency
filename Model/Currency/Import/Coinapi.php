<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model\Currency\Import;

use Kozeta\Currency\Model\Currency\Datafeed;
use Magento\Store\Model\ScopeInterface;

/**
 * Currency rate import from https://coinapi.io/
 */
class Coinapi extends \Magento\Directory\Model\Currency\Import\AbstractImport
{
    /**
     * @var string
     */
    const CURRENCY_CONVERTER_URL = 'https://rest.coinapi.io/v1/exchangerate/{{CURRENCY_FROM}}?apikey={{APIKEY}}';

    /** @var \Magento\Framework\Json\Helper\Data */
    private $jsonHelper;

    /**
     * Http Client Factory
     *
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    private $httpClientFactory;

    /**
     * Core scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Datafeed
     *
     * @var \Kozeta\Currency\Model\Currency\Datafeed
     */
    private $dataFeed;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Kozeta\Currency\Model\Currency\Datafeed $dataFeed
    ) {
        
        parent::__construct($currencyFactory);
        $this->scopeConfig = $scopeConfig;
        $this->httpClientFactory = $httpClientFactory;
        $this->jsonHelper = $jsonHelper;
        $this->dataFeed = $dataFeed;
        $this->dataFeed->setDatafeed([]);
    }

    /**
     * @param string $currencyFrom
     * @param string $currencyTo
     * @param int $retry
     * @return float|null
     */
    protected function _convert($currencyFrom, $currencyTo, $retry = 0)
    {
        $feed = $this->dataFeed->getDatafeed();
        if (!empty($feed)) {
            if (isset($feed[$currencyTo])) {
                return (float) $feed[$currencyTo]['rate'];
            }
                return 1;
        }

        $result = null;
        $timeout = (int)$this->scopeConfig->getValue('currency/coinapi/timeout', ScopeInterface::SCOPE_STORE);
        $apiKey = $this->scopeConfig->getValue('currency/coinapi/api_key', ScopeInterface::SCOPE_STORE);
        $url = str_replace('{{CURRENCY_FROM}}', $currencyFrom, self::CURRENCY_CONVERTER_URL);
        $url = str_replace('{{APIKEY}}', $apiKey, $url);

        /** @var \Magento\Framework\HTTP\ZendClient $httpClient */
        $httpClient = $this->httpClientFactory->create();

        try {
            $response = $httpClient->setUri($url)
                ->setConfig(['timeout' => $timeout])
                ->request('GET')
                ->getBody();
            $rates = $this->jsonHelper->jsonDecode($response);

            $feed = [];
            foreach ($rates['rates'] as $data) {
                $feed[$data['asset_id_quote']] = $data;
            }

            if (isset($feed[$currencyTo])) {
                $result = $feed[$currencyTo]['rate'];
                $this->dataFeed->setDatafeed($feed);
                unset($rates);
            } else {
                $this->_messages[] = __('We can\'t retrieve the rates from url %1.', $url);
            }
        } catch (\Exception $e) {
            if ($retry == 0) {
                $this->_convert($currencyFrom, $currencyTo, 1);
            } else {
                $this->_messages[] = __('We can\'t retrieve the rates from url %1.', $url);
            }
        }
        return $result;
    }
}
