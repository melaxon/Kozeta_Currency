<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Plugin\Integration;

# Remove standard magento ACL for currency symbols

class Data
{
    public function beforeMapResources(\Magento\Integration\Helper\Data $helper, array $resources)
    {
        $restricted = $this->getRestrictedIds();
        foreach ($resources as $key => $resource) {
            if (in_array($resource['id'], $restricted)) {
                unset($resources[$key]);
            }
        }
        return [$resources];
    }
    private function getRestrictedIds()
    {
        return [
            'Magento_CurrencySymbol::symbols',
        ];
    }
}
