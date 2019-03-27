<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Plugin\Block;

/**
 * Currency dropdown block. Override Magento function to get currency names for switcher
 */
class Currency extends \Magento\Directory\Block\Currency
{
    /**
     * Retrieve currencies array
     * Return array: code => currency name
     * Return empty array if only one currency
     *
     * @return array
     * Uncomment this and comment the below function to use created currency names in switcher
     */
    public function aroundGetCurrencies(
        \Magento\Directory\Block\Currency $subject,
        callable $proceed
    ) {
        $currencies = $subject->getData('currencies');
        if ($currencies === null) {
            $currencies = [];
            $codes = $this->_storeManager
                ->getStore()
                ->getAvailableCurrencyCodes(true);
            if (is_array($codes) && count($codes) > 1) {
                $rates = $this->_currencyFactory->create()->getCurrencyRates(
                    $this->_storeManager->getStore()->getBaseCurrency(),
                    $codes
                );
                $names = $this->_currencyFactory->create()->getCurrencyNames($codes);
                foreach ($codes as $code) {
                    if (isset($rates[$code])) {
                        if (!empty($names[$code])) {
                            $currencies[$code] = __($names[$code]);
                        } else {
                            $currencies[$code] = $code;
                        }
                    }
                }
            }
            $this->setData('currencies', $currencies);
        }

        return $currencies;
    }
 /*
  * Initial  function utiizing CurrencyBundle
  * Uncomment this and comment the above function to use existing bundled currency names in switcher (if not exist - use created names)
  */
//     public function getCurrencies(
//         \Magento\Directory\Model\PriceCurrency $subject,
//         callable $proceed,
//)
//     {
//         $currencies = $this->getData('currencies');
//         if ($currencies === null) {
//             $currencies = [];
//
//             $codes = $this->_storeManager->getStore()->getAvailableCurrencyCodes(true);
//             if (is_array($codes) && count($codes) > 1) {
//                 $rates = $this->_currencyFactory->create()->getCurrencyRates(
//                     $this->_storeManager->getStore()->getBaseCurrency(),
//                     $codes
//              );
//              $names = $this->_currencyFactory->create()->getCurrencyNames($codes);
//
//                 foreach ($codes as $code) {
//                     if (isset($rates[$code])) {
//                         $allCurrencies = (new \Magento\Framework\Locale\Bundle\CurrencyBundle())->get(
//                             $this->localeResolver->getLocale()
//                         )['Currencies'];
//
//                         $currencies[$code] = $allCurrencies[$code][1] ?: __($names[$code]) ?: $code;
//                     }
//                 }
//             }
//
//             $this->setData('currencies', $currencies);
//         }
//         return $currencies;
//     }
}
