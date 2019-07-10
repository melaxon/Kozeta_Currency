<?php

/**
 * Copyright © 2018 Altcheckout. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Kozeta\Currency\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Api\DataObjectHelper;
use Kozeta\Currency\Api\Data\CoinInterface;
use Kozeta\Currency\Api\Data\CoinInterfaceFactory;
use Kozeta\Currency\Controller\Adminhtml\Coin;
use Kozeta\Currency\Api\CoinRepositoryInterface;
use Magento\Framework\Locale\Bundle\CurrencyBundle as CurrencyBundle;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /** @var \Magento\Framework\App\State **/
    private $state;
    
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    
    /*
     * @var CurrencyFactory
     */
    private $currencyFactory;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * Init
     * @param CoinRepositoryInterface $coinRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param CoinInterfaceFactory $coinFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param CurrencyFactory $currencyFactory
     * @param ResolverInterface $localeResolver
     * @param State $state
     */
    public function __construct(
        CoinRepositoryInterface $coinRepository,
        DataObjectHelper $dataObjectHelper,
        CoinInterfaceFactory $coinFactory,
        ScopeConfigInterface $scopeConfig,
        CurrencyFactory $currencyFactory,
        ResolverInterface $localeResolver,
        State $state
    ) {
        $this->coinRepository = $coinRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->coinFactory = $coinFactory;
        $this->scopeConfig = $scopeConfig;
        $this->currencyFactory = $currencyFactory;
        $this->localeResolver = $localeResolver;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);

        /** @var \Kozeta\Currency\Api\Data\CoinInterface $coin */
        $currencyModel = $this->currencyFactory->create();
        $codes = $currencyModel->getConfigAllowCurrencies();
        $names = $currencyModel->getCurrencyNames($codes);
        $currentSymbols = $this->_unserializeStoreConfig('currency/options/customsymbol');
        $allCurrencies = (new CurrencyBundle())->get($this->localeResolver->getLocale())['Currencies'];
        foreach ($codes as $code) {
            $name = $code;
            $symbol = $allCurrencies[$code][0] ?: $code;
            if ($allCurrencies[$code][1]) {
                $name = $allCurrencies[$code][1];
            }
            if (isset($currentSymbols[$code]) && !empty($currentSymbols[$code])) {
                $symbol = $currentSymbols[$code];
            }

            if (!empty($currencyModel->getCurrencyParamByCode($code))) {
                continue;
            }

            $data = $this->getCoinValues($code, $name, $symbol);
            $coin = $this->coinFactory->create();
            $this->dataObjectHelper->populateWithArray($coin, $data, CoinInterface::class);
            $this->coinRepository->save($coin);
        }
    }

    /**
     * Prepare currency params
     *
     * @param array
     * @return array
     */
    private function getCoinValues($code, $name, $symbol)
    {
        $allStoreViews = (int) \Magento\Cms\Ui\Component\Listing\Column\Cms\Options::ALL_STORE_VIEWS;
        return [
            'coin_id' => null,
            'name' => $name,
            'url_key' => strtolower($code),
            'description' => '',
            'code' => $code,
            'is_fiat' => 1,
            'type' => 1,
            'symbol' => $symbol,
            'meta_title' => '',
            'meta_description' => '',
            'meta_keywords' => '',
            'is_active' => 1,
            'in_rss' => 1,
            'sort_order' => 0,
            'txfee' => 0,
            'minconf' => 2,
            'currency_converter_id' => 'default',
            'precision' => 2,
            'store_id' => $allStoreViews,
            'checked' => true,
            'import_enabled' => 1,
            'import_scheduler' => 0,
        ];
    }

    /**
     * Unserialize data from Store Config.
     *
     * @param string $configPath
     * @param int $storeId
     * @return array
     */
    private function _unserializeStoreConfig($configPath, $storeId = null)
    {
        $result = [];
        $configData = (string) $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($configData) {
            if (class_exists(Json::class)) {
                $this->serializer = ObjectManager::getInstance()->get(Json::class);
                $result = $this->serializer->unserialize($configData);
            } else {
                try {
                    // @codingStandardsIgnoreStart
                    $result = unserialize($configData);
                     // @codingStandardsIgnoreEnd
                } catch (\Exception $e) {
                    $result = json_decode($configData, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \InvalidArgumentException(
                            'Unable to unserialize value.' . " $configPath : $configData; "
                        );
                    }
                }
            }
        }
        return is_array($result) ? $result : [];
    }
}
