<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Block\Adminhtml\Coin;

class Img extends \Magento\Framework\View\Element\Template
{
    /**
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getPlaceholderUrl()
    {
        return $this->getViewFileUrl('Kozeta_Currency::images/coin/placeholder/avatar.jpg');
    }
}
