<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Plugin\Sales\Model;

use Kozeta\Currency\Model\Precision;

class Order
{

    /**
     * @var \Kozeta\Currency\Model\Precision
     */
    private $precisionObject;

    public function __construct(
        Precision $precisionObject
    ) {
        $this->precisionObject = $precisionObject;
    }
    
    /**
     * @param Order $subject
     * mixed $proceed
     * array $args
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetTotalDue(\Magento\Sales\Model\Order $subject, callable $proceed, ...$args)
    {
        $code = $subject->getOrderCurrencyCode();
        $defaultStoreId = $subject->getDefaultStoreId();
        $precision = $this->precisionObject->getPrecisionByCode($code, $defaultStoreId);
        $total = $subject->getGrandTotal() - $subject->getTotalPaid();
        return round($total, $precision);
    }
}
