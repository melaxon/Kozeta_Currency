<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Controller\Adminhtml\Coin;

use \Kozeta\Currency\Controller\Adminhtml\Coin as CoinController;

class Index extends CoinController
{
    /**
     * Coin grid.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Kozeta_Currency::coin');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Currencies'));
        $resultPage->addBreadcrumb(__('Currency'), __('Currency'));
        $resultPage->addBreadcrumb(__('Manage Currencies'), __('Manage Currencies'));
        return $resultPage;
    }
}
