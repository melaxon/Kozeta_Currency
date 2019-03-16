<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model\Coin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Rss
{
    /**
     * @var string
     */
    const RSS_PAGE_URL                  = 'currency/coin/rss';
    /**
     * @var string
     */
    const COIN_RSS_ACTIVE_CONFIG_PATH = 'currency/coin/rss';
    /**
     * @var string
     */
    const GLOBAL_RSS_ACTIVE_CONFIG_PATH = 'rss/config/active';
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @return bool
     */
    public function isRssEnabled()
    {
        return
            $this->scopeConfig->getValue(self::GLOBAL_RSS_ACTIVE_CONFIG_PATH, ScopeInterface::SCOPE_STORE) &&
            $this->scopeConfig->getValue(self::COIN_RSS_ACTIVE_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getRssLink()
    {
        return $this->urlBuilder->getUrl(
            self::RSS_PAGE_URL,
            ['store' => $this->storeManager->getStore()->getId()]
        );
    }
}
