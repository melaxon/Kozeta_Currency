<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model\Currency\Import\Source;

/**
 * @schedule
 */
class Schedule implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('Default schedule')
            ],
            [
                'value' => 1,
                'label' => __('Alternative schedule 1')
            ],
            [
                'value' => 2,
                'label' => __('Alternative schedule 2')
            ]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            0 => __('Default schedule'),
            1 => __('Alternative schedule 1'),
            2 => __('Alternative schedule 2')
        ];
    }
}
