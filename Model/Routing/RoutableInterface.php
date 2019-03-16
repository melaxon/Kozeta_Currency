<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model\Routing;

interface RoutableInterface
{
    /**
     * @param $urlKey
     * @param $storeId
     * @return int|null
     */
    public function checkUrlKey($urlKey, $storeId);
}
