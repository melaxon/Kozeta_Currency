<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Currency
 */

namespace Kozeta\Currency\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Store\Model\Store;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Precision extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    const XML_PATH_PRICE_PRECISION = 'catalog_price_precision/general/price_precision';


    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;

    }

    protected function _construct()
    {
        $this->_init(\Kozeta\Currency\Model\ResourceModel\Precision::class);
    }

    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }

    /**
     * Return Config Value by XML Config Path
     * @param $path
     * @param $scopeType
     *
     * @return mixed
     */
    public function getValueByPath($path, $scopeType = 'website')
    {
        return $this->getScopeConfig()->getValue($path, $scopeType);
    }

    /**
     * Return Price precision from store config
     *
     * @return mixed
     */
    public function getConfigPricePrecision()
    {
        return $this->getValueByPath(self::XML_PATH_PRICE_PRECISION, 'website');
    }


    /**
     * Return precision: default, fixed or for given currency
     * @param $currencyType
     *
     * @return int
     */
    public function getPrecision($currencyType = 'display')
    {
        
        $configPrecision = $this->getConfigPricePrecision();
        
        if ($configPrecision === 'default') {
        	return (int) PriceCurrencyInterface::DEFAULT_PRECISION; 
        }
        if ($configPrecision != 'auto') {
        	$result = $configPrecision;
        	return (int) $result;
        }
		$store = $this->_storeManager->getStore()->getId();
		if($currencyType == 'base') {
			$code = $this->_storeManager->getStore()->getBaseCurrency()->getCode();
		}
		else {
			$code = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
		}
		$result = $this->_getResource()->getPrecisionByCode($code, $store);
		if(empty($result) && $result !== '0') {
			$defaultStoreId = Store::DEFAULT_STORE_ID;
			if ($store != $defaultStoreId) {
				$result = $this->_getResource()->getPrecisionByCode($code, $defaultStoreId);
			}
		} 
		if(empty($result) && $result !== '0') {
			$result = PriceCurrencyInterface::DEFAULT_PRECISION; 
		}
		return (int) $result;

    }
    
    public function getPrecisionByCode($code, $store = Store::DEFAULT_STORE_ID) {
    	return $this->_getResource()->getPrecisionByCode($code, $store);
    }

}