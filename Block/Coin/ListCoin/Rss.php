<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Block\Coin\ListCoin;

use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Kozeta\Currency\Model\Coin;
use Kozeta\Currency\Model\Coin\Rss as RssModel;
use Kozeta\Currency\Model\Coin\Url;
use Kozeta\Currency\Model\ResourceModel\Coin\CollectionFactory;

class Rss extends AbstractBlock implements DataProviderInterface
{
    /**
     * @var string
     */
    const CACHE_LIFETIME_CONFIG_PATH = 'currency/coin/rss_cache';

    /**
     * @var \Kozeta\Currency\Model\Coin\Rss
     */
    protected $rssModel;

    /**
     * @var \Kozeta\Currency\Model\Coin\Url
     */
    protected $urlModel;

    /**
     * @var \Kozeta\Currency\Model\ResourceModel\Coin\CollectionFactory
     */
    protected $coinCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param RssModel $rssModel
     * @param Url $urlModel
     * @param CollectionFactory $coinCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        RssModel $rssModel,
        Url $urlModel,
        CollectionFactory $coinCollectionFactory,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->rssModel = $rssModel;
        $this->urlModel = $urlModel;
        $this->coinCollectionFactory = $coinCollectionFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        $storeId = (int)$this->getRequest()->getParam('store_id');
        if ($storeId == null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $storeId;
    }

    /**
     * @return array
     */
    public function getRssData()
    {
        $url = $this->urlModel->getListUrl();
        $data = [
            'title' => __('Coins'),
            'description' => __('Coins'),
            'link' => $url,
            'charset' => 'UTF-8'
        ];
        $collection = $this->coinCollectionFactory->create();
        $collection->addStoreFilter($this->getStoreId());
        $collection->addFieldToFilter('is_active', Coin::STATUS_ENABLED);
        $collection->addFieldToFilter('in_rss', 1);
        foreach ($collection as $item) {
            /** @var \Kozeta\Currency\Model\Coin $item */
            $description = '<table><tr><td><a href="%s">%s</a></td></tr></table>';
            $description = sprintf($description, $item->getCoinUrl(), $item->getName());
            $data['entries'][] = [
                'title' => $item->getName(),
                'link' => $item->getCoinUrl(),
                'description' => $description,
            ];
        }
        return $data;
    }

    /**
     * Check if RSS feed allowed
     *
     * @return mixed
     */
    public function isAllowed()
    {
        return $this->rssModel->isRssEnabled();
    }

    /**
     * Get information about all feeds this Data Provider is responsible for
     *
     * @return array
     */
    public function getFeeds()
    {
        $feeds = [];
        $feeds[] = [
            'label' => __('Coins'),
            'link' => $this->rssModel->getRssLink(),
        ];
        $result = ['group' => __('Currency'), 'feeds' => $feeds];
        return $result;
    }

    /**
     * @return bool
     */
    public function isAuthRequired()
    {
        return false;
    }

    /**
     * @return int
     */
    public function getCacheLifetime()
    {
        $lifetime = $this->_scopeConfig->getValue(
            self::CACHE_LIFETIME_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
        return $lifetime ?: null;
    }
}
