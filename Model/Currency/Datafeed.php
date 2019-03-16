<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model\Currency;

/**
 * Currency rate import model (From https://frankfurter.app/)
 */
class Datafeed
{

	public $feed;

	public function getDatafeed()
	{
		return $this->feed;
	}
	
	public function setDatafeed($dataFeed)
	{
		$this->feed = $dataFeed;
	}
}