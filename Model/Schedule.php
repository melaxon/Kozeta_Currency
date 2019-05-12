<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Directory\Model\Observer as ModelObserver;
use Psr\Log\LoggerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface as inlineTranslation;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\Currency\Import\Factory as ImportFactory;

class Schedule
{
    const MINUTEWISCE_MPORT_ENABLE = 'currency/import/enabled_minutewice_schedule';
    const IMPORT_SCHEDULER_DEFAULT = 0;
    const IMPORT_SCHEDULER_1 = 1;
    const IMPORT_SCHEDULER_2 = 2;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Directory\Model\Observer
     */
    private $_observer;

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
        ModelObserver $_observer,
        LoggerInterface $logger,
        TransportBuilder $transportBuilder,
        inlineTranslation $inlineTranslation,
        CurrencyFactory $currencyFactory,
        ImportFactory $importFactory
    ) {
        $this->logger = $logger;
        $this->_observer = $_observer;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->_currencyFactory = $currencyFactory;
        $this->_importFactory = $importFactory;
    }


    /**
     * @param string $path
     * @return boolean
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function getPathEnable($path)
    {
        if (!$this->_scopeConfig->getValue(
            'currency/import/enabled_minutewice_schedule',
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
     * @param mixed $schedule
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
        } 
return;
        $this->scheduledUpdateCurrencyRatesAlt($schedule, self::IMPORT_SCHEDULER_DEFAULT);
    }
    
    /**
     * @param mixed $schedule
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function updateCurrencyRatesAlt1($schedule)
    {
        if (!$this->getPathEnable('currency/import_alt_1/enabled') || !$this->getPathValue('currency/import_alt_1/schedule')) {
            return;
        }
return;
        $this->scheduledUpdateCurrencyRatesAlt($schedule, self::IMPORT_SCHEDULER_1);
    }
    
    /**
     * @param mixed $schedule
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function updateCurrencyRatesAlt2($schedule)
    {
        if (!$this->getPathEnable('currency/import_alt_2/enabled') || !$this->getPathValue('currency/import_alt_2/schedule')) {
            return;
        }
return;
        $this->scheduledUpdateCurrencyRatesAlt($schedule, self::IMPORT_SCHEDULER_2);
    }
    
    /**
     * @param mixed $schedule
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function scheduledUpdateCurrencyRatesAlt($schedule, $scheduler = self::IMPORT_SCHEDULER_DEFAULT)
    {
        $scheduler = (int) $scheduler;
        $importWarnings = [];
        $errors = [];
        $rates = [];
        $services = [];
        $defaultService = $this->getPathValue('currency/import/service');

        if (!$defaultService) {
            $importWarnings[] = __('FATAL ERROR:') . ' ' . __('Please specify the correct Default Import Service.');
            $this->sendErrorMessage($importWarnings);
            return;
        }

        $currencyModel = $this->_currencyFactory->create();
        $currencies = $currencyModel->getConfigAllowCurrencies();

        foreach ($currencies as $k => $code) {

//echo "<pre>for $code (SCHEDULER = $scheduler)\n";
            $import_enabled = (int) $currencyModel->getCurrencyParamByCode($code, 'import_enabled')[$code];
//echo "import_enabled = $import_enabled \n";

            if (!$import_enabled) {
//echo "UNSET $code  \n";                
                unset($currencies[$k]);
                continue;
            }


            
            $import_scheduler = (int) $currencyModel->getCurrencyParamByCode($code, 'import_scheduler')[$code];
//echo "import_scheduler = $import_scheduler \n";

            if ($import_scheduler != $scheduler) {
//echo "UNSET $code  \n"; 
                unset($currencies[$k]);
                continue;
            }
//echo "\n</pre>";
            $coinService = $currencyModel->getCurrencyParamByCode($code, 'currency_converter_id')[$code];
            if ($coinService == 'default') {
                $coinService = $defaultService;
            }

            $errMsg = [];
            if (!$coinService) {
                if (!$defaultService) {
                    unset($currencies[$k]);
                    $errMsg[] = __('FATAL ERROR:') . ' ' . __('Please specify either or both Default Import Service and the correct Import Service for %1', $code);
                    $this->sendErrorMessage($err);
                }
                $coinService = $defaultService;
            }
            $services[$coinService][] = $code;
        }
//        $services = array_unique($services);
        
        if (empty($currencies)) {
            return;
        }
echo "<pre>CURRENCIES \n";
print_r($currencies);
echo "\n</pre>";
        
echo "<pre>SERVICES \n";
print_r($services);
echo "\n</pre>";

        list($baseCurrency) = $currencyModel->getConfigBaseCurrencies();

        foreach ($services as $service => $_currencies) {

            if (empty($_currencies)) {
                continue;
            }
            $this->currencies = $_currencies;
echo "<pre>service = $service \nCURRENCIES:\n";
print_r($_currencies);
echo "\n</pre>";
            try {
                $importModel = $this->_importFactory->create($service);

                $rates = $importModel->fetchRates();
echo "<pre>RATES:\n";
print_r($rates);
echo "\n</pre>";
                $errors = $importModel->getMessages();

            } catch (\Exception $e) {
                $this->currencies = false;
                $importWarnings[] = __('FATAL ERROR:') . " ($service): " . __("The import model can't be initialized. Verify the model and try again.");
                //$this->sendErrorMessage($importWarnings);
                //throw $e;
            }
            if (sizeof($errors) > 0) {
                foreach ($errors as $error) {
                    $importWarnings[] = __('WARNING:') . ' ' . $error;
                }
            }

            if (sizeof($importWarnings) > 0) {
                $this->sendErrorMessage($importWarnings);
                continue;
            }
            $this->currencies = false;
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

    /**
     * Current set of currencies
     *
     * @return array
     */
    public function getCurrencies()
    {
        return $this->currencies;
    }
}