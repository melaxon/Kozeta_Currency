<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Plugin\Controller\Adminhtml\System\Currency;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\CurrencySymbol\Controller\Adminhtml\System\Currency as CurrencyAction;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Backend\Model\Session;

class FetchRates extends CurrencyAction
{
    /**
     * Fetch rates action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function aroundExecute(
        \Magento\CurrencySymbol\Controller\Adminhtml\System\Currency\FetchRates $subject,
        \Closure $proceed
    ) {
        $service = $subject->getRequest()->getParam('rate_services');
        if ($service != 'default') {
            return $proceed();
        }

        /** @var \Magento\Backend\Model\Session $backendSession */
        $backendSession = $this->_objectManager->get(Session::class);
        try {
            $this->_getSession()->setCurrencyRateService($service);

            $currencyModel = $this->_objectManager->get(CurrencyFactory::class)->create();

            $currencies = $currencyModel->getConfigAllowCurrencies();
            
            $defaultService = $this->_objectManager->get(ScopeConfigInterface::class)->getValue(
                'currency/import/service',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $baseCurrency = $currencyModel->getConfigBaseCurrencies();
            $services = [];
            foreach ($currencies as $key => $code) {
                $importEnabled = $currencyModel->getCurrencyParamByCode($code, 'import_enabled');
                if (in_array($code, $baseCurrency)) {
                    $importEnabled[$code] = 1;
                }

                if (empty($importEnabled)) {
                    unset($currencies[$key]);
                    $this->messageManager->addWarning(
                        __('ERROR:') . ' ' . __('Settings for currency %1 is incorrect. Please make sure %1 is installed and check currency settings.', $code)
                    );
                    continue;
                }

                $importEnabled[$code] = (int) $importEnabled[$code];
                if (!$importEnabled[$code]) {
                    unset($currencies[$key]);
                    continue;
                }
            
                $coinService = $currencyModel->getCurrencyParamByCode($code, 'currency_converter_id')[$code];
                if ($coinService == 'default') {
                    $coinService = $defaultService;
                }

                if (!$coinService) {
                    if (!$defaultService) {
                        unset($currencies[$key]);
                        $this->messageManager->addWarning(
                            __('ERROR:') . ' ' . __('Please specify either or both Default Import Service and the correct Import Service for %1', $code)
                        );
                        continue;
                    }
                    $coinService = $defaultService;
                }
                $services[$coinService][] = $code;
            }

            $runtimeCurrencies = $this->_objectManager->get(\Kozeta\Currency\Model\Currency\RuntimeCurrencies::class);
            $rates = [];
            $_errors = [];
            foreach ($services as $service => $_currencies) {
                if (empty($_currencies)) {
                    continue;
                }
                
                $runtimeCurrencies->setImportCurrencies($_currencies);
                try {
                    /** @var \Magento\Directory\Model\Currency\Import\ImportInterface $importModel */
                    $importModel = $this->_objectManager->get(\Magento\Directory\Model\Currency\Import\Factory::class)
                        ->create($service);
                } catch (\Exception $e) {
                    $runtimeCurrencies->setImportCurrencies(false);
                    throw new LocalizedException(
                        __("The import model can't be initialized. Verify the model and try again. ($service)")
                    );
                }
                $_rates = $importModel->fetchRates();
                foreach ($_rates as $baseCurrency => $c) {
                    foreach ($c as $code => $rate) {
                        if (!isset($rates[$baseCurrency][$code])) {
                            $rates[$baseCurrency][$code] = $rate;
                        }
                    }
                }
                $_errors[] = $importModel->getMessages();
            }
            foreach ($_errors as $errors) {
                if (!empty($errors)) {
                    foreach ($errors as $error) {
                        $this->messageManager->addWarning($error);
                    }
                }
            }
            $this->messageManager->addSuccess(__('Click "Save" to apply the rates we found.'));
            $backendSession->setRates($rates);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('adminhtml/*/');
    }

    public function execute()
    {
    }
}
