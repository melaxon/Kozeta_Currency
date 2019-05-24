<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Block\Adminhtml\Coin;

use Magento\Framework\View\Element\Template\Context;

class Img extends \Magento\Framework\View\Element\Template
{
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getPlaceholderUrl()
    {
        return $this->getViewFileUrl('Kozeta_Currency::images/coin/placeholder/avatar.jpg');
    }
}
