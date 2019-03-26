<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Api\Data;

/**
 * @api
 */
interface CoinInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const COIN_ID           = 'coin_id';
    const NAME              = 'name';
    const URL_KEY           = 'url_key';
    const IS_ACTIVE         = 'is_active';
    const TYPE              = 'type';
    const SORT_ORDER        = 'sort_order';
    const AVATAR            = 'avatar';
    const META_TITLE        = 'meta_title';
    const META_DESCRIPTION  = 'meta_description';
    const META_KEYWORDS     = 'meta_keywords';
    const STORE_ID          = 'store_id';
    const IN_RSS            = 'in_rss';
    const DESCRIPTION       = 'description';
    const CODE              = 'code';
    const IS_FIAT           = 'is_fiat';
    const SYMBOL            = 'symbol';
    const TXFEE             = 'txfee';
    const MINCONF           = 'minconf';
    const PRECISION         = 'precision';
    const CURRENCY_CONVERTER_ID = 'currency_converter_id';
//    const UPDATED_AT        = 'updated_at';
//    const RATE            	= 'rate';


    /**
     * Get sort order
     *
     * @return string|null
     */
    public function getSortOrder();

    /**
     * Set sort order
     *
     * @param string $sortOrder
     * @return CoinInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Get minconf
     *
     * @return string
     */
    public function getMinconf();

    /**
     * Set minconf
     * @param $minconf
     * @return CoinInterface
     */
    public function setMinconf($minconf);

    /**
     * Get precision
     *
     * @return string
     */
    public function getPrecision();

    /**
     * Set precision
     * @param $precision
     * @return CoinInterface
     */
    public function setPrecision($precision);

    /**
     * Get currency_converter_id
     *
     * @return string
     */
    public function getCurrencyConverterId();

    /**
     * Set CurrencyConverterId
     * @param $currencyConverterId
     * @return CoinInterface
     */
    public function setCurrencyConverterId($currencyConverterId);

    /**
     * Get url key
     *
     * @return string
     */
    public function getUrlKey();

    /**
     * Get is active
     *
     * @return bool|int
     */
    public function getIsActive();

    /**
     * Get in rss
     *
     * @return bool|int
     */
    public function getInRss();

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getProcessedDescription();

    /**
     * Get CODE
     *
     * @return string
     */
    public function getCode();

    /**
     * Get type
     *
     * @return int
     */
    public function getType();

    /**
     * Get isFiat
     *
     * @return string
     */
    public function getIsFiat();

    /**
     * Get avatar
     *
     * @return string
     */
    public function getAvatar();

    /**
     * Get rate
     *
     * @return string
     */
//    public function getRate();

    /**
     * Get symbol
     *
     * @return string
     */
    public function getSymbol();

    /**
     * set id
     *
     * @param $id
     * @return CoinInterface
     */
    public function setId($id);

    /**
     * set name
     *
     * @param $name
     * @return CoinInterface
     */
    public function setName($name);

    /**
     * set url key
     *
     * @param $urlKey
     * @return CoinInterface
     */
    public function setUrlKey($urlKey);

    /**
     * Set is active
     *
     * @param $isActive
     * @return CoinInterface
     */
    public function setIsActive($isActive);

    /**
     * Set in rss
     *
     * @param $inRss
     * @return CoinInterface
     */
    public function setInRss($inRss);

    /**
     * Set description
     *
     * @param $description
     * @return CoinInterface
     */
    public function setDescription($description);

    /**
     * Set CODE
     *
     * @param $code
     * @return CoinInterface
     */
    public function setCode($code);

    /**
     * set type
     *
     * @param $type
     * @return CoinInterface
     */
    public function setType($type);

    /**
     * set isFiat
     *
     * @param $isFiat
     * @return CoinInterface
     */
    public function setIsFiat($isFiat);

    /**
     * set avatar
     *
     * @param $avatar
     * @return CoinInterface
     */
    public function setAvatar($avatar);

    /**
     * set rate
     *
     * @param $rate
     * @return CoinInterface
     */
//    public function setRate($rate);

    /**
     * Set symbol
     *
     * @param $symbol
     * @return CoinInterface
     */
    public function setSymbol($symbol);

    /**
     * Get txfee
     *
     * @return string
     */
    public function getTxfee();

    /**
     * set txfee
     *
     * @param $txfee
     * @return CoinInterface
     */
    public function setTxfee($txfee);

    /**
     * Get updated at
     *
     * @return string
     */
//    public function getUpdatedAt();

    /**
     * set updated at
     *
     * @param $updatedAt
     * @return CoinInterface
     */
//    public function setUpdatedAt($updatedAt);

    /**
     * @param $storeId
     * @return CoinInterface
     */
    public function setStoreId($storeId);

    /**
     * @return int[]
     */
    public function getStoreId();

    /**
     * @return string
     */
    public function getMetaTitle();

    /**
     * @param $metaTitle
     * @return CoinInterface
     */
    public function setMetaTitle($metaTitle);

    /**
     * @return string
     */
    public function getMetaDescription();

    /**
     * @param $metaDescription
     * @return CoinInterface
     */
    public function setMetaDescription($metaDescription);

    /**
     * @return string
     */
    public function getMetaKeywords();

    /**
     * @param $metaKeywords
     * @return CoinInterface
     */
    public function setMetaKeywords($metaKeywords);
}
