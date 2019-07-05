<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model\Currency\Import;

use Kozeta\Currency\Model\Currency\Datafeed;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Currency rate import from https://www.coinpayments.net/
 */
class Coinpayments extends \Magento\Directory\Model\Currency\Import\AbstractImport
{
    /**
     * @var string
     */
    const CP_API_URL = 'https://www.coinpayments.net/api.php';
    
    /**
     * @var Curl
     */
    private $_curl;

    /**
     * Core scope config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Datafeed
     *
     * @var Datafeed
     */
    private $dataFeed;

    /**
     * Initialize dependencies
     *
     * @param CurrencyFactory $currencyFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param Curl $curl
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function __construct(
        CurrencyFactory $currencyFactory,
        ScopeConfigInterface $scopeConfig,
        Datafeed $dataFeed,
        Curl $curl
    ) {
        
        parent::__construct($currencyFactory);
        $this->scopeConfig = $scopeConfig;
        $this->dataFeed = $dataFeed;
        $this->dataFeed->setDatafeed([]);
        $this->_curl = $curl;
    }

    /**
     * Calculate currency rates through BTC
     *
     * @param $feed
     * @param $currencyFrom
     * @param $currencyTo
     * return (float) $rate or false
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function calculateRate($feed, $currencyFrom, $currencyTo)
    {
        if ($currencyTo == 'BTC') {
            if (isset($feed[$currencyFrom])) {
                return $feed[$currencyFrom]['rate'];
            }
        }
        if ($currencyFrom == 'BTC') {
            if (isset($feed[$currencyTo])) {
                $feed[$currencyTo]['rate'] = (float) $feed[$currencyTo]['rate'];
                if ($feed[$currencyTo]['rate'] == 0) {
                    $this->_messages[] = __('The rate of currency %1 nears to zero.', $currencyTo);
                    return false;
                }
                return 1 / $feed[$currencyTo]['rate'];
            }
        }
        if (isset($feed[$currencyFrom])) {
            $feed[$currencyFrom]['rate'] = (float) $feed[$currencyFrom]['rate'];
            if ($feed[$currencyFrom]['rate'] == 0) {
                $this->_messages[] = __('The rate of currency %1 nears to zero.', $currencyFrom);
                return false;
            }
            if (isset($feed[$currencyTo])) {
                if ($feed[$currencyTo]['rate'] == 0) {
                    $this->_messages[] = __('The rate of currency %1 nears to zero.', $currencyTo);
                    return false;
                }
                $feed[$currencyTo]['rate'] = (float) $feed[$currencyTo]['rate'];
                return $feed[$currencyFrom]['rate'] / $feed[$currencyTo]['rate'];
            }
            $this->_messages[] = __('Currency (To) %1 is not present in Coinpayments datafeed.', $currencyTo);
            return false;
        }
        $this->_messages[] = __('Currency (From) %1 is not present in Coinpayments datafeed.', $currencyFrom);
        return false;
    }

    /**
     * @param string $currencyFrom
     * @param string $currencyTo
     * @param int $retry
     * @return float|null
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _convert($currencyFrom, $currencyTo, $retry = 0, $short = 1)
    {
      
        //get saved datafeed
        $feed = $this->dataFeed->getDatafeed();
        if (!empty($feed)) {
            return $this->calculateRate($feed, $currencyFrom, $currencyTo);
        }
        $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        
        $timeout = (int)$this->scopeConfig->getValue('currency/coinpayments/timeout', $scope);
        $publicKey = $this->scopeConfig->getValue('currency/coinpayments/public_key', $scope);
        $privateKey = $this->scopeConfig->getValue('currency/coinpayments/private_key', $scope);
        $url = self::CP_API_URL;
        $data = [
            'version' => 1,
            'cmd' => 'rates',
            'key' => $publicKey,
            'accepted' => 1,
            'format' => 'json',
            'short' => $short
        ];

        try {
            $this->_curl->addHeader('HMAC', hash_hmac('sha512', http_build_query($data), $privateKey));
            $this->_curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
            $this->_curl->post($url, $data);
            $response = $this->_curl->getBody();
            $response = json_decode($this->_curl->getBody());
            
            if ($response->error != 'ok') {
                $this->_messages[] = $response->error;
                return false;
            }

            $feed = [];
            foreach ($response->result as $key => $item) {
                $feed[$key] = [
                    'code' => $key,
                    'is_fiat' => $item->is_fiat,
                    'rate' => $item->rate_btc,
                    'last_update' => $item->last_update,
                    'tx_fee' => $item->tx_fee,
                    'status' => $item->status,
                    'accepted' => $item->accepted,
                ];
                if (!isset($data['short']) || $data['short'] == 0) {
                    $feed[$key]['name'] = $item->name;
                    $feed[$key]['confirms'] = $item->confirms;
                    $feed[$key]['can_convert'] = $item->can_convert;
                    $feed[$key]['capabilities'] = $item->capabilities;
                    $feed[$key]['explorer'] = $item->explorer;
                }
            }
            $this->dataFeed->setDatafeed($feed);

            return $this->calculateRate($feed, $currencyFrom, $currencyTo);
        } catch (\Exception $e) {
            if ($retry == 0) {
                $this->_convert($currencyFrom, $currencyTo, 1);
            } else {
                $this->_messages[] = __('We can\'t retrieve the rates from url %1.', $url);
            }
            throw $e;
        }
        return false;
    }
}
