<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Block\Adminhtml\System\Currency\Rate;

use Magento\Store\Model\ScopeInterface;

class Matrix extends \Magento\CurrencySymbol\Block\Adminhtml\System\Currency\Rate\Matrix
{
    /**
     * @var string
     */
    protected $_template = 'Kozeta_Currency::system/currency/rate/matrix.phtml';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var integer
     */
    protected $_coinsInRow;

    /**
     * @var string
     */
    const COINS_IN_ROW_MENU_CONFIG_PATH = 'currency/currency_rate_settings/coins_in_row';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Directory\Model\CurrencyFactory $dirCurrencyFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Directory\Model\CurrencyFactory $dirCurrencyFactory,
        array $data = [],
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_dirCurrencyFactory = $dirCurrencyFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $dirCurrencyFactory, $data);
    }
    
    /**
     * @return int
     */
    public function getCoinsInRow()
    {
    
        if ($this->_coinsInRow !== null) {
            return $this->_coinsInRow;
        }
        $this->_coinsInRow = (int) trim($this->scopeConfig->getValue(self::COINS_IN_ROW_MENU_CONFIG_PATH, ScopeInterface::SCOPE_STORES));
        if (!$this->_coinsInRow) {
            $this->_coinsInRow = 6;
        }
        return $this->_coinsInRow;
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
    public function _getDisplayRates()
    {
        $currencyModel = $this->_dirCurrencyFactory->create();
        $currencies = $currencyModel->getConfigAllowCurrencies();
        $defaultCurrencies = $currencyModel->getConfigBaseCurrencies();

        return $currencyModel->getCurrencyRates($defaultCurrencies, $currencies, true);
    }
    
}
