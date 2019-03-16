<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model;

class Output
{
    /**
     * @var \Zend_Filter_Interface
     */
    protected $templateProcessor;

    /**
     * @param \Zend_Filter_Interface $templateProcessor
     */
    public function __construct(
        \Zend_Filter_Interface $templateProcessor
    ) {
        $this->templateProcessor = $templateProcessor;
    }

    /**
     * @param $string
     * @return string
     */
    public function filterOutput($string)
    {
        return $this->templateProcessor->filter($string);
    }
}
