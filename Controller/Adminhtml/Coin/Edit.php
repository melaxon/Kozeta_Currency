<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Controller\Adminhtml\Coin;

use Kozeta\Currency\Controller\Adminhtml\Coin;
use Kozeta\Currency\Controller\RegistryConstants;

class Edit extends Coin
{
    /**
     * Initialize current coin and set it in the registry.
     *
     * @return int
     */
    protected function _initCoin()
    {
        $coinId = $this->getRequest()->getParam('coin_id');
        $this->coreRegistry->register(RegistryConstants::CURRENT_COIN_ID, $coinId);

        return $coinId;
    }

    /**
     * Edit or create coin
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $coinId = $this->_initCoin();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Kozeta_Currency::coin');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Currencies'));
        $resultPage->addBreadcrumb(__('Currency'), __('Currency'));
        $resultPage->addBreadcrumb(__('Manage Currencies'), __('Manage Currencies'), $this->getUrl('kozeta_currency/coin'));

        if ($coinId === null) {
            $resultPage->addBreadcrumb(__('New Coin'), __('New Coin'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Coin'));
        } else {
            $resultPage->addBreadcrumb(__('Edit Coin'), __('Edit Coin'));
            $resultPage->getConfig()->getTitle()->prepend(
                $this->coinRepository->getById($coinId)->getName()
            );
        }
        return $resultPage;
    }
}
