<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Plugin\Sales\Model;

class Order
{

    /**
     * @var \Kozeta\Currency\Model\Precision
     */
    protected $precisionObject;

    /**
     * @var
     */
//	protected $_precision;
	
	
	
    public function __construct(
		\Kozeta\Currency\Model\Precision $precisionObject
    ) {
		$this->precisionObject = $precisionObject;
    }
	
	public function aroundGetTotalDue(
        \Magento\Sales\Model\Order $subject,
        callable $proceed,
		...$args
	) {

		$code = $subject->getOrderCurrencyCode();
		$defaultStoreId = $subject->getDefaultStoreId();
		$precision = $this->precisionObject->getPrecisionByCode($code, $defaultStoreId);
		$total = $subject->getGrandTotal() - $subject->getTotalPaid();
		return round($total,$precision);
	}
}

