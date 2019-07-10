<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Block\Coin;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Kozeta\Currency\Block\ImageBuilder;
use Kozeta\Currency\Model\ResourceModel\Coin\CollectionFactory as CoinCollectionFactory;
use Magento\Directory\Model\CurrencyFactory;

class ViewCoin extends Template
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var ImageBuilder
     */
    private $imageBuilder;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    public $priceCurrencyObject;
    
    /**
     * @var store
     */
    private $store;
    
    public $currencyFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param $imageBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ImageBuilder $imageBuilder,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrencyObject,
        \Magento\Store\Model\Store $store,
        CurrencyFactory $currencyFactory,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->imageBuilder = $imageBuilder;
        $this->priceCurrencyObject = $priceCurrencyObject;
        $this->store = $store;
        $this->currencyFactory = $currencyFactory;
        parent::__construct($context, $data);
    }

    public function _getRate($code = null)
    {

        return $this->currencyFactory->create()->getCurrencyRates(
            $this->_storeManager->getStore()->getBaseCurrency()->getCode(),
            $code,
            true
        );
    }

    /**
     * get current coin
     *
     * @return \Kozeta\Currency\Model\Coin
     */
    public function getCurrentCoin()
    {
        return $this->coreRegistry->registry('current_coin');
    }

    /**
     * @return store default display currency
     */
    public function _getDefaultCurrencyCode()
    {
        return $this->_storeManager->getStore()->getDefaultCurrencyCode();
    }

    /**
     * @return Base currency
     */
    public function _getBaseCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
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
}
