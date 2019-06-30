<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model\Currency;

/**
 * Currency rate import model
 */
class Datafeed
{

    private $feed;

    public function getDatafeed()
    {
        return $this->feed;
    }
    
    public function setDatafeed($dataFeed)
    {
        $this->feed = $dataFeed;
    }
}
