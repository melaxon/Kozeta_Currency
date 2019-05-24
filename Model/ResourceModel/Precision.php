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
    private static $precisionCache;
    
    /**
     * @var string
     */
    private $coinStoreTable;

    protected function _construct()
    {
        $this->_init('kozeta_currency_coin', 'code');
        $this->coinStoreTable = $this->getTable('kozeta_currency_coin_store');
    }

    public function getPrecisionByCode($code, $store = \Magento\Store\Model\Store::DEFAULT_STORE_ID)
    {
        if (!isset(self::$precisionCache[$code][$store])) {
            $stores = [$store];
            if ($store != \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
                $stores[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            }
            $connection = $this->getConnection();
            $storeCondition = 'coin_store.store_id IN (?)';
            $select = $connection
                ->select()
                ->from(
                    ['coin' => $this->getMainTable()],
                    'precision'
                )
                ->join(
                    ['coin_store' => $this->coinStoreTable],
                    'coin.coin_id = coin_store.coin_id',
                    []
                )
                ->where(
                    'coin.code = ?',
                    $code
                )
                ->where(
                    $storeCondition,
                    $stores
                );
            self::$precisionCache[$code][$store] = $connection->fetchOne($select);
        }
        return self::$precisionCache[$code][$store];
    }
}
