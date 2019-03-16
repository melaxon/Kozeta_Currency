<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Controller\Adminhtml\Coin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Kozeta\Currency\Controller\Adminhtml\Coin;

class Delete extends Coin
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('coin_id');
        if ($id) {
            try {
                $this->coinRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The coin has been deleted.'));
                $resultRedirect->setPath('kozeta_currency/*/');
                return $resultRedirect;
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('The coin no longer exists.'));
                return $resultRedirect->setPath('kozeta_currency/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('kozeta_currency/coin/edit', ['coin_id' => $id]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('There was a problem deleting the coin'));
                return $resultRedirect->setPath('kozeta_currency/coin/edit', ['coin_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a coin to delete.'));
        $resultRedirect->setPath('kozeta_currency/*/');
        return $resultRedirect;
    }
}
