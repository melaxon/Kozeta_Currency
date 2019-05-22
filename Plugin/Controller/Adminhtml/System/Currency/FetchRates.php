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

class FetchRates extends CurrencyAction
{

    /**
     * @var array
     */
    private $currencies;
    /**
     * @var Schedule
     */

    /**
     * Fetch rates action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function aroundExecute(
        \Magento\CurrencySymbol\Controller\Adminhtml\System\Currency\FetchRates $subject,
        \Closure $proceed
    ) {
        $service = $this->getRequest()->getParam('rate_services');
        if ($service != 'default') {
            return $proceed();
        }

        /** @var \Magento\Backend\Model\Session $backendSession */
        $backendSession = $this->_objectManager->get(\Magento\Backend\Model\Session::class);
        try {
            $this->_getSession()->setCurrencyRateService($service);

            $currencyModel = $this->_objectManager->get(\Magento\Directory\Model\CurrencyFactory::class)->create();

            $currencies = $currencyModel->getConfigAllowCurrencies();
            
            $defaultService = $this->_objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class)->getValue(
            'currency/import/service',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

            $services = [];
            foreach ($currencies as $k => $code) {
                $import_enabled = $currencyModel->getCurrencyParamByCode($code, 'import_enabled');
                if (empty($import_enabled)) {
                    unset($currencies[$k]);
                    $this->messageManager->addWarning(
                            __('FATAL ERROR:') . ' ' . __('Settings for currency %1 is incorrect. Please re-save General -> Currency settings and Advanced -> Systen -> Installed currencies', $code)
                    );
                    continue;
                }

                $import_enabled[$code] = (int) $import_enabled[$code];
                if (!$import_enabled[$code]) {
                    unset($currencies[$k]);
                    continue;
                }
            
                $coinService = $currencyModel->getCurrencyParamByCode($code, 'currency_converter_id')[$code];
                if ($coinService == 'default') {
                    $coinService = $defaultService;
                }

                if (!$coinService) {
                    if (!$defaultService) {
                        unset($currencies[$k]);
                        $this->messageManager->addWarning(
                            __('FATAL ERROR:') . ' ' . __('Please specify either or both Default Import Service and the correct Import Service for %1', $code)
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
                        __("The import model can't be initialized. Verify the model and try again.")
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
                if (sizeof($errors) > 0) {
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
