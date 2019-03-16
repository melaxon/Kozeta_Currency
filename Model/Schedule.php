<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model;

class Schedule
{
    const IMPORT_ENABLE = 'currency/import/enabled_minutewice_schedule';

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Directory\Model\Observer
     */
	protected $_observer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
	protected $logger;

    public function __construct(
    	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\Observer $_observer,
        \Psr\Log\LoggerInterface $logger
    ) {
		$this->logger = $logger;
		$this->_observer = $_observer;
		$this->_scopeConfig = $scopeConfig;
    }



    /**
     * @param mixed $schedule
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function updateCurrencyRates($schedule)
    {
    	if (!$this->_scopeConfig->getValue(
            self::IMPORT_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        )
    	{
            $this->logger->info('CURRENCY RATES MINUTE-WICE IMPORT DISABLED');
            return;
        }
        
    	$res = $this->_observer->scheduledUpdateCurrencyRates($schedule);	
    	$this->logger->info('CURRENCY RATES IMPORTED' . $res);
    		
    }

}


