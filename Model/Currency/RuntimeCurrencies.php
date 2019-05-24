<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model\Currency;

/*
 * Model Currency
 *
 */
class RuntimeCurrencies
{
    /**
     * @var Schedule
     */
    protected static $_instance;

    /**
     * @var array
     */
    private $currencies;

    /**
     * Retrieve Schedule object
     *
     * @return Schedule
     * @throws \RuntimeException
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof \Kozeta\Currency\Model\Currency\RuntimeCurrencies) {
            throw new \RuntimeException('RuntimeCurrencies object isn\'t initialized');
        }
        return self::$_instance;
    }

    public function __construct()
    {
        self::$_instance = $this;
    }

    /**
     * Current set of currencies
     *
     * @return array
     */
    public function getImportCurrencies()
    {
        return $this->currencies;
    }
    
    /**
     * Current set of currencies
     *
     * @return array
     */
    public function setImportCurrencies($_currencies)
    {
        $this->currencies = $_currencies;
    }
}
