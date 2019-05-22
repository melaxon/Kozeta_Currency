<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Plugin\Config;

//use Magento\Framework\App\Config\Value;
use Kozeta\Currency\Model\Coin;
use Magento\Framework\Locale\ConfigInterface;
use Kozeta\Currency\Model\ResourceModel\Coin\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;

class AddCurrencies
{
    /**
     * @var \Kozeta\Currency\Model\ResourceModel\Coin\CollectionFactory
     */
    private $collectionFactory;
    
    /**
     * Installed currencies
     */
    const XML_PATH_CURRENCY_INSTALLED = 'system/currency/installed';
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    
    /**
     * New currencies list
     *
     * @var array
     */
    private $coins;
    
    /**
     * HTTP request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @param ConfigInterface $config
     * @param CollectionFactory $collectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param Http $request
     */
    public function __construct(
//        ConfigInterface $config,
        CollectionFactory $collectionFactory,
        ScopeConfigInterface $scopeConfig,
        Http $request
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
    }

    public function getNewCurrencies()
    {
        
        if ($this->coins) {
            return $this->coins;
        }
        $store_id = (int) $this->request->getParam('store', 0);
        $currencies = [];
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect('*');
        $collection->addFieldToFilter('is_active', Coin::STATUS_ENABLED);
        $collection->setOrder('name', 'ASC');
        $collection->addStoreFilter($store_id);
        foreach ($collection as $_coin) {
            $_coin->getIsActive() == false ?: $currencies[] = [
                'value' => $_coin->getCode(),
                'label' => __($_coin->getName())
            ];
        }
        $this->coins = $this->_sortOptionArray($currencies);
        
        return $this->coins;
    }

    protected function _sortOptionArray($option)
    {
        $data = [];
        foreach ($option as $item) {
            $data[$item['value']] = $item['label'];
        }
        asort($data);
        $option = [];
        foreach ($data as $key => $label) {
            $option[] = ['value' => $key, 'label' => $label];
        }
        return $option;
    }

    /**
     * @inheritdoc
     */
    public function aroundGetOptionCurrencies($subject, \Closure $proceed, ...$args)
    {
        $installedCurrencies = $this->getNewCurrencies();
        
        $selectedCurrencies = explode(
            ',',
            $this->scopeConfig->getValue(
                'system/currency/installed',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        foreach ($installedCurrencies as $k => $c) {
            if (!in_array($c['value'], $selectedCurrencies)) {
                unset($installedCurrencies[$k]);
            }
        }
        return $installedCurrencies;
    }

    /**
     * @inheritdoc
     */
    public function aroundGetOptionAllCurrencies($subject, \Closure $proceed, ...$args)
    {
        return $this->getNewCurrencies() ?: [];
    }
}
