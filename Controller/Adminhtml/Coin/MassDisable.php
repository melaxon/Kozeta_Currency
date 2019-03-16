<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Controller\Adminhtml\Coin;

use Kozeta\Currency\Model\Coin;

class MassDisable extends MassAction
{
    /**
     * @var bool
     */
    protected $isActive = false;

    /**
     * @param Coin $coin
     * @return $this
     */
    protected function massAction(Coin $coin)
    {
        $coin->setIsActive($this->isActive);
        $this->coinRepository->save($coin);
        return $this;
    }
}
