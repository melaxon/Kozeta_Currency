<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model\Currency\Import\Source;

use \Magento\Directory\Model\Currency\Import\Source\Service as Options;

/**
 * Currency rates import Service
 *
 * @method array toOptionArray()
 */
class Service extends Options
{
    /**
     * @var array
     */
    private $_options;

    /**
     * @param \Magento\Directory\Model\Currency\Import\Source\Service $options
     */
    public function __construct(
        Options $options
    ) {
        $this->_options = $options->toOptionArray();
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        array_unshift($this->_options, [
            'label' => __('Use default settings'),
            'value' => 'default',
        ]);

        return $this->_options;
    }
}
