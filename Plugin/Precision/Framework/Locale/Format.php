<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Plugin\Precision\Framework\Locale;

class Format
{

    /**
     * @var \Kozeta\Currency\Model\Precision
     */
    private $precisionObject;

    /**
     * @var integer
     */
    private $precision;

    public function __construct(
        \Kozeta\Currency\Model\Precision $precisionObject
    ) {
        $this->precisionObject = $precisionObject;
        $this->precision = $this->precisionObject->getPrecision();
    }
    
    public function afterGetPriceFormat($subject, $result)
    {
        $result['precision'] = $this->precision;
        $result['requiredPrecision'] = $this->precision;
        return $result;
    }
}
