<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Block\Coin;

use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;
use Kozeta\Currency\Model\Coin;
use Kozeta\Currency\Model\ResourceModel\Coin\CollectionFactory as CoinCollectionFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Kozeta\Currency\Block\ImageBuilder;
use Magento\Store\Model\StoreManagerInterface;

class ListCoin extends Template
{
    /**
     * @var string
     */
    const COIN_PAGES_CONFIG_PATH = 'currency/coin/coin_pages';

    /**
     * @var ImageBuilder
     */
    protected $imageBuilder;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var ListCoin
     */
    public $enableCoinPages;
    
    /**
     * @var CoinCollectionFactory
     */
    protected $coinCollectionFactory;
    
    /**
     * @var UrlFactory
     */
    protected $urlFactory;

    /**
     * @var \Kozeta\Currency\Model\ResourceModel\Coin\Collection
     */
    protected $coins;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;
    
    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    public $currencyFactory;
    
    /**
     * @param Context $context
     * @param CoinCollectionFactory $coinCollectionFactory
     * @param UrlFactory $urlFactory
     * @param CurrencyFactory $currencyFactory,
     * @param ScopeConfigInterface $scopeConfig,
     * @param ImageBuilder $imageBuilder,
     * @param StoreManagerInterface $storeManager,
     * @param array $data
     */
    public function __construct(
        Context $context,
        CoinCollectionFactory $coinCollectionFactory,
        UrlFactory $urlFactory,
        CurrencyFactory $currencyFactory,
        ScopeConfigInterface $scopeConfig,
        ImageBuilder $imageBuilder,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->coinCollectionFactory = $coinCollectionFactory;
        $this->urlFactory = $urlFactory;
        $this->currencyFactory = $currencyFactory;
        $this->scopeConfig = $scopeConfig;
        $this->imageBuilder = $imageBuilder;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function getEnableCoinPages()
    {
        if ($this->enableCoinPages === null) {
            $this->enableCoinPages = $this->scopeConfig->getValue(
                self::COIN_PAGES_CONFIG_PATH,
                ScopeInterface::SCOPE_STORE
            );
        }
        return $this->enableCoinPages;
    }

    /**
     * @return \Kozeta\Currency\Model\ResourceModel\Coin\Collection
     */
    public function getCoins()
    {
        if ($this->coins === null) {
            $this->coins = $this->coinCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter(
                    'is_active',
                    Coin::STATUS_ENABLED
                )
                //->addFieldToFilter(\Kozeta\Currency\Api\Data\CoinInterface::STORE_ID,['eq' => $this->storeManager->getStore()->getId()])
                ->addStoreFilter($this->storeManager->getStore()->getId())
                ->setOrder('sort_order', 'ASC')
                ->setOrder('is_fiat', 'ASC')
                ->setOrder('name', 'ASC');
        }

        return $this->coins;
    }

    /**
     * @return all rates
     */
    public function _getRates()
    {
        $codes = [];
        foreach ($this->getCoins() as $coin) {
            $codes[] = $coin->getCode();
        }

        $rates = $this->currencyFactory->create()->getCurrencyRates(
            $this->storeManager->getStore()->getBaseCurrency(),
            $codes,
            true
        );

        return $rates;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var \Magento\Theme\Block\Html\Pager $pager */
        $pager = $this->getLayout()->createBlock(Pager::class, 'currency.coin.list.pager');
        $pager->setCollection($this->getCoins());
        $this->setChild('pager', $pager);
        $this->getCoins()->load();
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    /**
     * @param $entity
     * @param $imageId
     * @param array $attributes
     * @return \Kozeta\Currency\Block\Image
     */
    public function getImage($entity, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setEntity($entity)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }

    /**
     * @return Base currency
     */
    public function getBaseCurrencyCode()
    {
        return $this->storeManager->getStore()->getBaseCurrencyCode();
    }
}
