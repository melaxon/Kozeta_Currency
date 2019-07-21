<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Plugin\Block;

/**
 * around plugin for getCurrency names for switcher
 */
class Currency extends \Magento\Directory\Block\Currency
{
    /**
     * Retrieve currencies array
     * Return array: code => currency name
     * Return empty array if only one currency
     *
     * @return array
     */
    public function aroundGetCurrencies(
        \Magento\Directory\Block\Currency $subject,
        callable $proceed
    ) {
        $currencies = $subject->getData('currencies');
        if ($currencies !== null) {
            return $proceed();
        }
        $currencies = [];
        $codes = $this->_storeManager->getStore()->getAvailableCurrencyCodes(true);
        if (!is_array($codes) || count($codes) <= 1) {
            return [];
        }
        $rates = $this->_currencyFactory->create()->getCurrencyRates(
            $this->_storeManager->getStore()->getBaseCurrency(),
            $codes
        );
        $names = $this->_currencyFactory->create()->getCurrencyNames($codes);
// uncomment this to use Bundled names when possible
//                $allCurrencies = (new \Magento\Framework\Locale\Bundle\CurrencyBundle())->get(
//                     $this->localeResolver->getLocale()
//                )['Currencies'];

        foreach ($codes as $code) {
            if (!isset($rates[$code])) {
                continue;
            }
// uncomment this to use Bundled names when possible
//                    if (null !== $allCurrencies[$code][1]) {
//                        $currencies[$code] = $allCurrencies[$code][1];
//                        continue;
//                    }
            if (!empty($names[$code])) {
                $currencies[$code] = $names[$code];
                continue;
            }
            $currencies[$code] = $code;
        }
        $subject->setData('currencies', $currencies);
        
        return $currencies;
    }
}
