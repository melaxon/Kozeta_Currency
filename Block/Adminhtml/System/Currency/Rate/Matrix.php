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
use Magento\Framework\App\Config\ScopeConfigInterface;
use Kozeta\Currency\Model\Schedule;
use Magento\Directory\Model\Currency\Import\Config as ImportConfig;

class Matrix extends \Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate\Matrix
{
    /**
     * @var string
     */
    protected $_template = 'Kozeta_Currency::system/currency/rate/matrix.phtml';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var integer
     */
    private $coinsInRow;

    /**
     * @var ImportConfig
     */
    private $importConfig;

    /**
     * @var string
     */
    const COINS_IN_ROW_MENU_CONFIG_PATH = 'currency/currency_rate_settings/coins_in_row';

    /**
     * @param Context $context
     * @param CurrencyFactory $dirCurrencyFactory
     * @param ScopeConfigInterface $scopeConfig,
     * @param ImportConfig $importConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        CurrencyFactory $dirCurrencyFactory,
        ScopeConfigInterface $scopeConfig,
        ImportConfig $importConfig,
        array $data = []
    ) {
        $this->_dirCurrencyFactory = $dirCurrencyFactory;
        $this->scopeConfig = $scopeConfig;
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

        $this->coinsInRow = (int) trim($this->scopeConfig->getValue(self::COINS_IN_ROW_MENU_CONFIG_PATH, ScopeInterface::SCOPE_STORES));
        if (!$this->coinsInRow) {
            $this->coinsInRow = 6;
        }
        return $this->coinsInRow ?: 6;
    }
    
    /**
     * @return int
     */
    public function getRows()
    {
        return ceil(count($this->getAllowedCurrencies()) / $this->getCoinsInRow());
    }
    
    /**
     * @return array
     */
    public function getDisplayRates()
    {
        $currencyModel = $this->_dirCurrencyFactory->create();
        $currencies = $currencyModel->getConfigAllowCurrencies();
        $defaultCurrencies = $currencyModel->getConfigBaseCurrencies();
        return $currencyModel->getCurrencyRates($defaultCurrencies, $currencies, true);
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
}
