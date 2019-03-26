<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Currency
 */

namespace Kozeta\Currency\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;

class Precision extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Precision cache array
     *
     * @var array
     */
    protected static $_precisionCache;
    
    protected $_coinStoreTable;
    protected function _construct()
    {
        $this->_init('kozeta_currency_coin', 'code');
        $this->_coinStoreTable = $this->getTable('kozeta_currency_coin_store');
    }

    public function getPrecisionByCode($code, $store)
    {
        if (!isset(self::$_precisionCache[$code][$store])) {
            $connection = $this->getConnection();
            $storeCondition = 'coin_store.store_id IN (?)';
            $select = $connection
                ->select()
                ->from(
                    ['coin' => $this->getMainTable()],
                    'precision'
                )
                ->join(
                    ['coin_store' => $this->_coinStoreTable],
                    'coin.coin_id = coin_store.coin_id',
                    []
                )
                ->where(
                    'coin.code = ?',
                    $code
                )
                ->where(
                    $storeCondition,
                    [$store]
                );
            
            self::$_precisionCache[$code][$store] = $connection->fetchOne($select);
        }
        return self::$_precisionCache[$code][$store];
    }
}
