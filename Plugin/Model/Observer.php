<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

/**
 * Plugin for Directory module observer
 */
namespace Kozeta\Currency\Plugin\Model;

class Observer
{
    /**
     * @param mixed $schedule
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundScheduledUpdateCurrencyRates(
        \Magento\Directory\Model\Observer $subject,
        \Closure $proceed,
        ...$args
    )
    {
        return false;
    }
}

