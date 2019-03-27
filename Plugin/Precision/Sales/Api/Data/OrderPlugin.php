<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Plugin\Precision\Sales\Api\Data;

class OrderPlugin
{

    /**
     * @var \Kozeta\Currency\Model\Precision
     */
    protected $precisionObject;
    
    
    
    public function __construct(
        \Kozeta\Currency\Model\Precision $precisionObject
    ) {
        $this->precisionObject = $precisionObject;
    }



    /**
     * @param \Magento\Sales\Model\Order $subject
     * @param array ...$args
     * @return array
     */
    public function beforeFormatPricePrecision(
        \Magento\Sales\Model\Order $subject,
        ...$args
    ) {
        $orderCurrency = $subject->getOrderCurrencyCode();
        $baseCurrency = $subject->getBaseCurrencyCode();
        $store = $subject->getStoreId();
        $precision = $this->precisionObject->getPrecisionByCode($orderCurrency, $store);
        $args[1] = $precision;
        return $args;
    }
    /**
     * @param \Magento\Sales\Model\Order $subject
     * @param array ...$args
     * @return array
     */
    public function beforeFormatBasePricePrecision(
        \Magento\Sales\Model\Order $subject,
        ...$args
    ) {
        $orderCurrency = $subject->getOrderCurrencyCode();
        $baseCurrency = $subject->getBaseCurrencyCode();
        $store = $subject->getStoreId();
        $precision = $this->precisionObject->getPrecision('base');
        $args[1] = $precision;
        return $args;
    }
}
