<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model;

use Kozeta\Currency\Model\Routing\RoutableInterface;

interface FactoryInterface
{
    /**
     * @return RoutableInterface
     */
    public function create();
}
