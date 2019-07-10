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
use Magento\Directory\Model\Observer;

class Schedule
{
    const MINUTEWICE_IMPORT_ENABLE = 'currency/import/enabled_minutewice_schedule';
    const IMPORT_SCHEDULER_DEFAULT = 0;
    const IMPORT_SCHEDULER_1 = 1;
    const IMPORT_SCHEDULER_2 = 2;
    const SCHEDULE1_ENABLED = 'currency/import_alt_1/enabled';
    const SCHEDULE2_ENABLED = 'currency/import_alt_2/enabled';
    const SCHEDULE1_VALUE = 'currency/import_alt_1/schedule';
    const SCHEDULE2_VALUE = 'currency/import_alt_2/schedule';

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
    protected static $instance;

    /**
     * Retrieve Schedule object
     *
     * @return Schedule
     * @throws \RuntimeException
     *
     * @codingStandardsIgnoreStart
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof \Kozeta\Currency\Model\Schedule) {
            throw new \RuntimeException('Schedule object isn\'t initialized');
        }
        return self::$instance;
    }

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    private $currencyFactory;

    /**
     * @var \Magento\Directory\Model\Currency\Import\Factory
     */
    private $importFactory;

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
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->currencyFactory = $currencyFactory;
        $this->importFactory = $importFactory;
        $this->runtimeCurrencies = $runtimeCurrencies;
    }


    /**
     * @param string $path
     * @return boolean
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getPathEnable($path)
    {
        if (!$this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
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
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function updateCurrencyRatesNativeSchedule($schedule)
    {
        if (!$this->getPathEnable(Observer::IMPORT_ENABLE) || ($this->getPathEnable(self::MINUTEWICE_IMPORT_ENABLE))) {
            return;
        }
        $this->scheduledUpdateCurrencyRatesAlt($schedule, self::IMPORT_SCHEDULER_DEFAULT);
    }
    
    /**
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function updateCurrencyRates($schedule)
    {
        if (!$this->getPathEnable(Observer::IMPORT_ENABLE) || (!$this->getPathEnable(self::MINUTEWICE_IMPORT_ENABLE))) {
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
        if (!$this->getPathEnable(self::SCHEDULE1_ENABLED) || !$this->getPathValue(self::SCHEDULE1_VALUE)) {
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
        if (!$this->getPathEnable(self::SCHEDULE2_ENABLED) || !$this->getPathValue(self::SCHEDULE2_VALUE)) {
            return;
        }
        $this->scheduledUpdateCurrencyRatesAlt($schedule, self::IMPORT_SCHEDULER_2);
    }
    
    /**
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function scheduledUpdateCurrencyRatesAlt($schedule, $scheduler = self::IMPORT_SCHEDULER_DEFAULT)
    {
        $scheduler = (int) $scheduler;
        $rates = [];
        $services = [];
        $defaultService = $this->getPathValue(Observer::IMPORT_SERVICE);
        $currencyModel = $this->currencyFactory->create();
        $currencies = $currencyModel->getConfigAllowCurrencies();
        $baseCurrency = $currencyModel->getConfigBaseCurrencies();

        foreach ($currencies as $key => $code) {
            $importEnabled = $currencyModel->getCurrencyParamByCode($code, 'import_enabled');

            if (in_array($code, $baseCurrency)) {
                $importEnabled[$code] = 1;
            }

            if (empty($importEnabled)) {
                $errMsg = [];
                unset($currencies[$key]);
                $errMsg[] = __('ERROR:') . ' ' . __('Settings for currency %1 is incorrect. Please make sure %1 is installed and check currency settings.', $code);
                $this->sendErrorMessage($errMsg);
                continue;
            }

            if (!isset($importEnabled[$code]) || !$importEnabled[$code]) {
                unset($currencies[$key]);
                continue;
            }

            $importScheduler = (int) $currencyModel->getCurrencyParamByCode($code, 'import_scheduler')[$code];
            if ($importScheduler != $scheduler) {
                unset($currencies[$key]);
                continue;
            }

            $coinService = $currencyModel->getCurrencyParamByCode($code, 'currency_converter_id')[$code];
            if ($coinService == 'default') {
                $coinService = $defaultService;
            }

            if (!$coinService) {
                if (!$defaultService) {
                    $errMsg = [];
                    unset($currencies[$key]);
                    $errMsg[] = __('ERROR:') . ' ' . __('Please specify either or both Default Import Service and the correct Import Service for %1', $code);
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

        $importWarnings = [];
        $errors = [];
        foreach ($services as $_service => $_currencies) {
            if (empty($_currencies)) {
                continue;
            }
            $this->runtimeCurrencies->setImportCurrencies($_currencies);

            try {
                $importModel = $this->importFactory->create($_service);
                $rates = $importModel->fetchRates();
                $errors = $importModel->getMessages();
            } catch (\Exception $e) {
                $this->runtimeCurrencies->setImportCurrencies(false);
                $importWarnings[] = __('FATAL ERROR:') . " ($_service, $scheduler): " . __("The import model can't be initialized. Verify the model and try again.");
            }
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $importWarnings[] = __('WARNING:') . " ($_service) " . $error;
                }
            }

            if (!empty($importWarnings)) {
                $this->sendErrorMessage($importWarnings);
            }
            $service = [];
            foreach ($rates as $currencyCode => $rate) {
                foreach ($rate as $currencyTo => $value) {
                    $service[$currencyCode][$currencyTo] = $_service;
                }
            }

            $this->runtimeCurrencies->setImportCurrencies($_currencies);
            $this->currencyFactory->create()->saveRates($rates, $service);
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
            $this->transportBuilder->setTemplateIdentifier(
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
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();

            $this->inlineTranslation->resume();
        }
    }
}
