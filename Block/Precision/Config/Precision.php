<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Block\Precision\Config;

class Precision implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Return array of options
     *
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('0')],
            ['value' => 2, 'label' => __('2')],
            ['value' => 4, 'label' => __('4')],
            ['value' => 6, 'label' => __('6')],
            ['value' => 8, 'label' => __('8')],
            ['value' => 'auto', 'label' => __('Auto')],
            ['value' => 'default', 'label' => __('Default')],
        ];
    }
}