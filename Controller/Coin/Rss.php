<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Controller\Coin;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;

class Rss extends \Magento\Rss\Controller\Feed\Index
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->getRequest()->setParam('type', 'coins');
        parent::execute();
    }
}
