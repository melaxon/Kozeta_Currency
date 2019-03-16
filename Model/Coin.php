<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\Collection\Db;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Kozeta\Currency\Api\Data\CoinInterface;
use Kozeta\Currency\Model\Coin\Url;
use Kozeta\Currency\Model\ResourceModel\Coin as CoinResourceModel;
use Kozeta\Currency\Model\Routing\RoutableInterface;
use Kozeta\Currency\Model\Source\AbstractSource;


/**
 * @method CoinResourceModel _getResource()
 * @method CoinResourceModel getResource()
 */
class Coin extends AbstractModel implements CoinInterface, RoutableInterface
{
    /**
     * @var int
     */
    const STATUS_ENABLED = 1;
    /**
     * @var int
     */
    const STATUS_DISABLED = 0;
    /**
     * @var Url
     */
    protected $urlModel;
    /**
     * cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'kozeta_currency_coin';

    /**
     * cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'kozeta_currency_coin';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'kozeta_currency_coin';

    /**
     * filter model
     *
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filter;

    /**
     * @var UploaderPool
     */
    protected $uploaderPool;

    /**
     * @var \Kozeta\Currency\Model\Output
     */
    protected $outputProcessor;

    /**
     * @var AbstractSource[]
     */
    protected $optionProviders;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Output $outputProcessor
     * @param UploaderPool $uploaderPool
     * @param FilterManager $filter
     * @param Url $urlModel
     * @param array $optionProviders
     * @param array $data
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Output $outputProcessor,
        UploaderPool $uploaderPool,
        FilterManager $filter,
        Url $urlModel,
        array $optionProviders = [],
        array $data = [],
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null
    )
    {
        $this->outputProcessor = $outputProcessor;
        $this->uploaderPool    = $uploaderPool;
        $this->filter          = $filter;
        $this->urlModel        = $urlModel;
        $this->optionProviders = $optionProviders;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CoinResourceModel::class);
    }

    /**
     * Get sort order
     *
     * @return string|null
     */
    public function getSortOrder()
    {
        return $this->getData(CoinInterface::SORT_ORDER);
    }

    /**
     * Set sort order
     *
     * @param string $sortOrder
     * @return CoinInterface
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(CoinInterface::SORT_ORDER, $sortOrder);
    }

    /**
     * Get in rss
     *
     * @return bool|int
     */
    public function getInRss()
    {
        return $this->getData(CoinInterface::IN_RSS);
    }

    /**
     * Get type
     *
     * @return int
     */
    public function getType()
    {
        return $this->getData(CoinInterface::TYPE);
    }

    /**
     * Get isFiat
     *
     * @return string
     */
    public function getIsFiat()
    {
        return (bool) $this->getData(CoinInterface::IS_FIAT);
    }

    /**
     * Get symbol
     *
     * @return string
     */
    public function getSymbol()
    {
        return $this->getData(CoinInterface::SYMBOL);
    }

    /**
     * set name
     *
     * @param $name
     * @return CoinInterface
     */
    public function setName($name)
    {
        return $this->setData(CoinInterface::NAME, $name);
    }

    /**
     * Get minconf
     *
     * @return string
     */
    public function getMinconf()
    {
        return (int) $this->getData(CoinInterface::MINCONF);
    }

    /**
     * set minconf
     *
     * @param $minconf
     * @return CoinInterface
     */
    public function setMinconf($minconf)
    {
        return $this->setData(CoinInterface::MINCONF, $minconf);
    }

    /**
     * Get precision
     *
     * @return string
     */
    public function getPrecision()
    {
        return (int) $this->getData(CoinInterface::PRECISION);
    }

    /**
     * Set precision
     *
     * @return CoinInterface
     */
    public function setPrecision($precision)
    {
        return $this->setData(CoinInterface::PRECISION, $precision);
    }


    /**
     * Get currency_converter_id
     *
     * @return string
     */
    public function getCurrencyConverterId()
    {
        return $this->getData(CoinInterface::CURRENCY_CONVERTER_ID);
    }

    /**
     * set currency_converter_id
     *
     * @param $currencyConverterId
     * @return CoinInterface
     */
    public function setCurrencyConverterId($currencyConverterId)
    {
        return $this->setData(CoinInterface::CURRENCY_CONVERTER_ID, $currencyConverterId);
    }

    /**
     * Set in rss
     *
     * @param $inRss
     * @return CoinInterface
     */
    public function setInRss($inRss)
    {
        return $this->setData(CoinInterface::IN_RSS, $inRss);
    }

    /**
     * Set description
     *
     * @param $description
     * @return CoinInterface
     */
    public function setDescription($description)
    {
        return $this->setData(CoinInterface::DESCRIPTION, $description);
    }

    /**
     * Set CODE
     *
     * @param $code
     * @return CoinInterface
     */
    public function setCode($code)
    {
        return $this->setData(CoinInterface::CODE, $code);
    }

    /**
     * set type
     *
     * @param $type
     * @return CoinInterface
     */
    public function setType($type)
    {
        return $this->setData(CoinInterface::TYPE, $type);
    }

    /**
     * set isFiat
     *
     * @param $isFiat
     * @return CoinInterface
     */
    public function setIsFiat($isFiat)
    {
        return $this->setData(CoinInterface::IS_FIAT, $isFiat);
    }

    /**
     * Set symbol
     *
     * @param $symbol
     * @return CoinInterface
     */
    public function setSymbol($symbol)
    {
        return $this->setData(CoinInterface::SYMBOL, $symbol);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(CoinInterface::NAME);
    }

    /**
     * Get url key
     *
     * @return string
     */
    public function getUrlKey()
    {
        return $this->getData(CoinInterface::URL_KEY);
    }

    /**
     * Get is active
     *
     * @return bool|int
     */
    public function getIsActive()
    {
        return (bool) $this->getData(CoinInterface::IS_ACTIVE);
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(CoinInterface::DESCRIPTION);
    }

    /**
     * @return mixed
     */
    public function getProcessedDescription()
    {
        return $this->outputProcessor->filterOutput($this->getDescription());
    }

    /**
     * Get CODE
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getData(CoinInterface::CODE);
    }

    /**
     * Get avatar
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->getData(CoinInterface::AVATAR);
    }

    /**
     * @return bool|string
     * @throws LocalizedException
     */
    public function getAvatarUrl()
    {
        $url = false;
        $avatar = $this->getAvatar();
        if ($avatar) {
            if (is_string($avatar)) {
                $uploader = $this->uploaderPool->getUploader('image');
                $url = $uploader->getBaseUrl().$uploader->getBasePath().$avatar;
            } else {
                throw new LocalizedException(
                    __('Something went wrong while getting the avatar url.')
                );
            }
        }
        return $url;
    }

    /**
     * Get txfee
     *
     * @return string
     */
    public function getTxfee()
    {
        return (float) $this->getData(CoinInterface::TXFEE);
    }

    /**
     * set url key
     *
     * @param $urlKey
     * @return CoinInterface
     */
    public function setUrlKey($urlKey)
    {
        return $this->setData(CoinInterface::URL_KEY, $urlKey);
    }

    /**
     * Set is active
     *
     * @param $isActive
     * @return CoinInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(CoinInterface::IS_ACTIVE, $isActive);
    }

    /**
     * set avatar
     *
     * @param $avatar
     * @return CoinInterface
     */
    public function setAvatar($avatar)
    {
        return $this->setData(CoinInterface::AVATAR, $avatar);
    }


    /**
     * set txfee
     *
     * @param $txfee
     * @return CoinInterface
     */
    public function setTxfee($txfee)
    {
        return $this->setData(CoinInterface::TXFEE, $txfee);
    }


    /**
     * Check if coin url key exists
     * return coin id if coin exists
     *
     * @param string $urlKey
     * @param int $storeId
     * @return int
     */
    public function checkUrlKey($urlKey, $storeId)
    {
        return $this->_getResource()->checkUrlKey($urlKey, $storeId);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param $storeId
     * @return CoinInterface
     */
    public function setStoreId($storeId)
    {
        $this->setData(CoinInterface::STORE_ID, $storeId);
        return $this;
    }

    /**
     * @return array
     */
    public function getStoreId()
    {
        return $this->getData(CoinInterface::STORE_ID);
    }

    /**
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->getData(CoinInterface::META_TITLE);
    }

    /**
     * @param $metaTitle
     * @return CoinInterface
     */
    public function setMetaTitle($metaTitle)
    {
        $this->setData(CoinInterface::META_TITLE, $metaTitle);
        return $this;
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->getData(CoinInterface::META_DESCRIPTION);
    }

    /**
     * @param $metaDescription
     * @return CoinInterface
     */
    public function setMetaDescription($metaDescription)
    {
        $this->setData(CoinInterface::META_DESCRIPTION, $metaDescription);
        return $this;
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->getData(CoinInterface::META_KEYWORDS);
    }

    /**
     * @param $metaKeywords
     * @return CoinInterface
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->setData(CoinInterface::META_KEYWORDS, $metaKeywords);
        return $this;
    }


    /**
     * sanitize the url key
     *
     * @param $string
     * @return string
     */
    public function formatUrlKey($string)
    {
        return $this->filter->translitUrl($string);
    }

    /**
     * @return mixed
     */
    public function getCoinUrl()
    {
        return $this->urlModel->getCoinUrl($this);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getIsActive();
    }

    /**
     * @param $attribute
     * @return string
     */
    public function getAttributeText($attribute)
    {
        if (!isset($this->optionProviders[$attribute])) {
            return '';
        }
        if (!($this->optionProviders[$attribute] instanceof AbstractSource)) {
            return '';
        }
        return $this->optionProviders[$attribute]->getOptionText($this->getData($attribute));
    }
}
