<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Block\Adminhtml\System\Currency\Rate;

use Magento\Store\Model\ScopeInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Directory\Model\CurrencyFactory;
use Kozeta\Currency\Model\Schedule;
use Magento\Directory\Model\Currency\Import\Config as ImportConfig;

class Matrix extends \Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate\Matrix
{
    /**
     * @var string
     */
    protected $_template = 'Kozeta_Currency::system/currency/rate/matrix.phtml';

    /**
     * @var integer
     */
    private $coinsInRow;

    /**
     * @var ImportConfig
     */
    private $importConfig;

    /**
     * @var array
     */
    private $baseCurrencies;

    /**
     * @var string
     */
    const COINS_IN_ROW_MENU_CONFIG_PATH = 'currency/currency_rate_settings/coins_in_row';

    /**
     * @var int
     */
    const DEFAULT_COINS_IN_ROW = 6;
    
    /**
     * @param Context $context
     * @param CurrencyFactory $dirCurrencyFactory
     * @param ImportConfig $importConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        CurrencyFactory $dirCurrencyFactory,
        ImportConfig $importConfig,
        array $data = []
    ) {
        $this->_dirCurrencyFactory = $dirCurrencyFactory;
        $this->importConfig = $importConfig;
        parent::__construct($context, $dirCurrencyFactory, $data);
    }
    
    /**
     * @return int
     */
    public function getCoinsInRow()
    {
        if ($this->coinsInRow !== null) {
            return $this->coinsInRow;
        }

        $this->coinsInRow = (int) trim($this->_scopeConfig->getValue(self::COINS_IN_ROW_MENU_CONFIG_PATH, ScopeInterface::SCOPE_STORES));
        if (!$this->coinsInRow) {
            $this->coinsInRow = self::DEFAULT_COINS_IN_ROW;
        }
        return $this->coinsInRow;
    }
    
    /**
     * @return int
     */
    public function getRows($base = null)
    {
        return ceil(count($this->getCurrencies($base)) / $this->getCoinsInRow());
    }
    
    /**
     * Unshift base currency to sorted currency list
     * @return array
     */
    public function getCurrencies($base = null)
    {
        if ($base === null) {
            return $this->getAllowedCurrencies();
        }
        $baseCurrencies = $this->getBaseCurrencies();
        $currencies = $baseCurrencies[$base];
        $_currencies = array_flip($currencies);

        if (isset($_currencies[$base])) {
            $k = $_currencies[$base];
            unset($currencies[$k]);
            array_unshift($currencies, $base);
        }

        return $currencies;
    }

    /**
     * get website scope data
     * @return array
     */
    public function getBaseCurrencies()
    {
        if ($this->baseCurrencies) {
            return $this->baseCurrencies;
        }

        $baseCurrencies = [];
        foreach ($this->_storeManager->getWebsites() as $w) {
            $bc = $w->getBaseCurrency()->getCode();
            $allowedCurrencies = [];
            $_stores = $w->getStores();
            if (is_array($_stores)) {
                $_allowedCurrencies = [];
                foreach ($_stores as $s) {
                    $_allowedCurrencies = $s->getAvailableCurrencyCodes();
                    if ($_allowedCurrencies) {
                        $allowedCurrencies = array_merge($allowedCurrencies, $_allowedCurrencies);
                    }
                }
            }

            $baseCurrencies[$bc] = isset($baseCurrencies[$bc]) ? array_merge($baseCurrencies[$bc], $allowedCurrencies) : $allowedCurrencies;
            $baseCurrencies[$bc] = array_unique($baseCurrencies[$bc]);
        }

        $this->baseCurrencies = $baseCurrencies;
        return $this->baseCurrencies;
    }
    
    /**
     * @return array
     */
    public function getDisplayRates()
    {
        $currencyModel = $this->_dirCurrencyFactory->create();
        $currencies = $currencyModel->getConfigAllowCurrencies();
        $defaultCurrencies = $currencyModel->getConfigBaseCurrencies();
        $rates = $currencyModel->getCurrencyRates($defaultCurrencies, $currencies, true);
        return $rates;
    }

    /**
     * @return array
     */
    public function getServiceNames()
    {
        $serviceOptions = [];
        foreach ($this->importConfig->getAvailableServices() as $serviceName) {
            $serviceOptions[$serviceName] = $this->importConfig->getServiceLabel($serviceName);
        }
        $serviceOptions['manually'] = __('Manually');
        $serviceOptions['_'] = __('_');
        return $serviceOptions;
    }

    /**
     * Remove exponential part of decimal
     * Trim meaningless trailing zeroz
     *
     * @param decimal $c
     * @return decimal
     */
    public function formatDecimal($c)
    {
        $c = (float) $c;
        $c = strpos($c, 'E') ? number_format($c, explode("-", $c)[1] + 14) : $c;
        return rtrim($c, "0");
    }
}
