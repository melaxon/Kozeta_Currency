<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Pricing\Render;

class ExtendedFinalPriceBox extends \Magento\Catalog\Pricing\Render\FinalPriceBox
{

    /*
     * AbstractBlock
     */
    public function getCacheLifetime()
    {
        return 1;
    }
}
