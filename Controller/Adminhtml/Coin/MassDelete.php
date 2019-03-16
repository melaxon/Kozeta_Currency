<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Controller\Adminhtml\Coin;

use Kozeta\Currency\Model\Coin;

class MassDelete extends MassAction
{
    /**
     * @param Coin $coin
     * @return $this
     */
    protected function massAction(Coin $coin)
    {
        $this->coinRepository->delete($coin);
        return $this;
    }
}
