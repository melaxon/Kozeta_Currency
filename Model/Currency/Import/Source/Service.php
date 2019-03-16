<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model\Currency\Import\Source;

class Service extends \Magento\Directory\Model\Currency\Import\Source\Service

{
    /**
     * @var array
     */
    private $_options;

    /**
     * @param \Magento\Directory\Model\Currency\Import\Source\Service $options
     */
    public function __construct(
    	\Magento\Directory\Model\Currency\Import\Source\Service $options
    )
    {
        $this->_options = $options->toOptionArray();
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
		$this->_options[] = [
			'label' => __('Use default settings'),
			'value' => 'default',
		];

        return $this->_options;
    }
}