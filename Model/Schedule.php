<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface as inlineTranslation;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\Currency\Import\Factory as ImportFactory;
use Kozeta\Currency\Model\Currency\RuntimeCurrencies;

class Schedule
{
    const MINUTEWISCE_MPORT_ENABLE = 'currency/import/enabled_minutewice_schedule';
    const IMPORT_SCHEDULER_DEFAULT = 0;
    const IMPORT_SCHEDULER_1 = 1;
    const IMPORT_SCHEDULER_2 = 2;

    /**
     * @var \Kozeta\Currency\Model\Currency\RuntimeCurrencies
     */
    private $runtimeCurrencies;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var Schedule
     */
    protected static $_instance;

    /**
     * Retrieve Schedule object
     *
     * @return Schedule
     * @throws \RuntimeException
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof \Kozeta\Currency\Model\Schedule) {
//            $this-> sendErrorMessage(['Schedule object isn\'t initialized']);
            throw new \RuntimeException('Schedule object isn\'t initialized');
        }
        return self::$_instance;
    }

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $_transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    private $_currencyFactory;

    /**
     * @var \Magento\Directory\Model\Currency\Import\Factory
     */
    private $_importFactory;

    /**
     * @var array
     */
    private $currencies;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        TransportBuilder $transportBuilder,
        inlineTranslation $inlineTranslation,
        CurrencyFactory $currencyFactory,
        ImportFactory $importFactory,
        RuntimeCurrencies $runtimeCurrencies
    ) {
        $this->logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->_currencyFactory = $currencyFactory;
        $this->_importFactory = $importFactory;
        $this->runtimeCurrencies = $runtimeCurrencies;
    }


    /**
     * @param string $path
     * @return boolean
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getPathEnable($path)
    {
        if (!$this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            $this->logger->info('CURRENCY RATES MINUTE-WICE IMPORT DISABLED');
            return false;
        }
        return true;
    }

    /**
     * @param string $path
     * @return boolean
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getPathValue($path)
    {
        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function updateCurrencyRates($schedule)
    {
        if (
            !$this->getPathEnable('currency/import/enabled') || (
                !$this->getPathValue('crontab/default/jobs/currency_rates_update/schedule/cron_expr') &&
                !$this->getPathEnable(self::MINUTEWISCE_MPORT_ENABLE)
            )
        ) {
            return;
        }
        $this->scheduledUpdateCurrencyRatesAlt($schedule, self::IMPORT_SCHEDULER_DEFAULT);
    }
    
    /**
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function updateCurrencyRatesAlt1($schedule)
    {
        if (!$this->getPathEnable('currency/import_alt_1/enabled') || !$this->getPathValue('currency/import_alt_1/schedule')) {
            return;
        }
        $this->scheduledUpdateCurrencyRatesAlt($schedule, self::IMPORT_SCHEDULER_1);
    }
    
    /**
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function updateCurrencyRatesAlt2($schedule)
    {
        if (!$this->getPathEnable('currency/import_alt_2/enabled') || !$this->getPathValue('currency/import_alt_2/schedule')) {
            return;
        }
        $this->scheduledUpdateCurrencyRatesAlt($schedule, self::IMPORT_SCHEDULER_2);
    }
    
    /**
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function scheduledUpdateCurrencyRatesAlt($schedule, $scheduler = self::IMPORT_SCHEDULER_DEFAULT)
    {
        $scheduler = (int) $scheduler;
        $rates = [];
        $services = [];
        $defaultService = $this->getPathValue('currency/import/service');
        $currencyModel = $this->_currencyFactory->create();
        $currencies = $currencyModel->getConfigAllowCurrencies();

        foreach ($currencies as $k => $code) {
            $import_enabled = (int) $currencyModel->getCurrencyParamByCode($code, 'import_enabled')[$code];
            if (!$import_enabled) {
                unset($currencies[$k]);
                continue;
            }

            $import_scheduler = (int) $currencyModel->getCurrencyParamByCode($code, 'import_scheduler')[$code];
            if ($import_scheduler != $scheduler) {
                unset($currencies[$k]);
                continue;
            }
            
            $coinService = $currencyModel->getCurrencyParamByCode($code, 'currency_converter_id')[$code];
            if ($coinService == 'default') {
                $coinService = $defaultService;
            }

            $errMsg = [];
            if (!$coinService) {
                if (!$defaultService) {
                    unset($currencies[$k]);
                    $errMsg[] = __('FATAL ERROR:') . ' ' . __('Please specify either or both Default Import Service and the correct Import Service for %1', $code);
                    $this->sendErrorMessage($errMsg);
                    continue;
                }
                $coinService = $defaultService;
            }
            $services[$coinService][] = $code;
        }
        
        if (empty($currencies)) {
            return;
        }

        list($baseCurrency) = $currencyModel->getConfigBaseCurrencies();

        $importWarnings = [];
        $errors = [];
        foreach ($services as $_service => $_currencies) {

            if (empty($_currencies)) {
                continue;
            }
            $this->runtimeCurrencies->setImportCurrencies($_currencies);

            try {
                $importModel = $this->_importFactory->create($_service);
                $rates = $importModel->fetchRates();
                $errors = $importModel->getMessages();
            } catch (\Exception $e) {
                $this->runtimeCurrencies->setImportCurrencies(false);
                $importWarnings[] = __('FATAL ERROR:') . " ($_service): " . __("The import model can't be initialized. Verify the model and try again.");
            }
            if (sizeof($errors) > 0) {
                foreach ($errors as $error) {
                    $importWarnings[] = __('WARNING:') . " ($_service) " . $error;
                }
            }

            if (sizeof($importWarnings) > 0) {
                $this->sendErrorMessage($importWarnings);
//                continue;
            }
            $service = [];
            foreach ($rates as $currencyCode => $rate) {
                foreach ($rate as $currencyTo => $value) {
                    $service[$currencyCode][$currencyTo] = $_service;
                }
            }

            $this->runtimeCurrencies->setImportCurrencies($_currencies);
            $this->_currencyFactory->create()->saveRates($rates, $service);
        }
    }


    /**
     * @param array $errorMessage
     * @return void
     */
    public function sendErrorMessage($errorMessage)
    {
        $errorRecipient = $this->getPathValue('currency/import/error_email');
        if ($errorRecipient) {
            $this->inlineTranslation->suspend();
            $this->_transportBuilder->setTemplateIdentifier(
                $this->getPathValue('currency/import/error_email_template')
            )->setTemplateOptions(
                [
                    'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )->setTemplateVars(
                ['warnings' => join("\n", $errorMessage)]
            )->setFrom(
                $this->getPathValue('currency/import/error_email_identity')
            )->addTo($errorRecipient);
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();

            $this->inlineTranslation->resume();
        }
    }
}