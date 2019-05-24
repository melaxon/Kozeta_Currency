<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Plugin\Model\Currency\Import\Source;

/**
 * Currency rates import Service
 *
 * @method array toOptionArray()
 */
class Service
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    /**
     * @param \Magento\Directory\Model\Currency\Import\Source\Service $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToOptionArray(
        \Magento\Directory\Model\Currency\Import\Source\Service $subject,
        $result
    ) {
        $controller = $this->request->getControllerName();
        $action     = $this->request->getActionName();
        switch ($controller) {
            case 'coin':
                array_unshift($result, [
                    'label' => __('Use default settings'),
                    'value' => 'default',
                ]);
                break;
            case 'system_currency':
                array_unshift($result, [
                    'label' => __('Use currency settings'),
                    'value' => 'default',
                ]);
                break;
        }
        return $result;
    }
}
