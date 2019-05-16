<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Plugin\Controller\Adminhtml\System\Currency;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\ObjectManagerInterface;



/**
 * Around plugin for adminhtml FetchRates.
 */
class FetchRates
{

    /*
     * @var ManagerInterface
     */
private $resultFactory;

    /*
     * @var ResultFactory
     */
private $messageManager;


    /**
     * @var ObjectManagerInterface
     */
private $objectManager;


    public function __construct(
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        ObjectManagerInterface $objectManager
    )
    {
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        $this->objectManager = $objectManager;
    }

    /**
     * Fetch rates action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function aroundExecute(
        \Magento\CurrencySymbol\Controller\Adminhtml\System\Currency\FetchRates $subject,
        \Closure $proceed
    ) {
        /** @var \Magento\Backend\Model\Session $backendSession */
        $backendSession = $this->objectManager->get(\Magento\Backend\Model\Session::class);
        
        try {
            $service = $subject->getRequest()->getParam('rate_services');
//echo "<pre>";
//print_r($service);
//exit;
            $backendSession->_getSession()->setCurrencyRateService($service);
            if (!$service) {
                throw new LocalizedException(__('The Import Service is incorrect. Verify the service and try again.'));
            }
            try {
                /** @var \Magento\Directory\Model\Currency\Import\ImportInterface $importModel */
                $importModel = $subject->objectManager->get(\Magento\Directory\Model\Currency\Import\Factory::class)
                    ->create($service);
            } catch (\Exception $e) {
                throw new LocalizedException(
                    __("The import model can't be initialized. Verify the model and try again.")
                );
            }
            $rates = $importModel->fetchRates();
            $errors = $importModel->getMessages();
            if (sizeof($errors) > 0) {
                foreach ($errors as $error) {
                    $this->messageManager->addWarning($error);
                }
                $this->messageManager->addWarning(
                    __('Click "Save" to apply the rates we found.')
                );
            } else {
                $this->messageManager->addSuccess(__('Click "Save" to apply the rates we found.'));
            }

            $backendSession->setRates($rates);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('adminhtml/*/');
        //return $proceed();
    }
}
