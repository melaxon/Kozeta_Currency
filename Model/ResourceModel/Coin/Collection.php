<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model\ResourceModel\Coin;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Kozeta\Currency\Model\Coin;
use Kozeta\Currency\Model\ResourceModel\Coin as CoinResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'coin_id';
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'kozeta_currency_coin_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'coin_collection';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $_joinedFields = [];

    /**
     * constructor
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param null $connection
     * @param AbstractDb $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        $connection = null,
        AbstractDb $resource = null
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Coin::class, CoinResourceModel::class);
        $this->_map['fields']['coin_id'] = 'main_table.coin_id';
        $this->_map['fields']['store_id'] = 'store_table.store_id';
        $this->_map['fields']['precision'] = 'main_table.`precision`';
    }

    /**
     * after collection load
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _afterLoad()
    {
        $this->performAfterLoad('kozeta_currency_coin_store', 'coin_id');
        //foreach ($this->getItems() as $item) {
            /** @var \Kozeta\Currency\Model\Coin $item */
            // do something
        //}
        return parent::_afterLoad();
    }

    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition, false);
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add filter by store
     *
     * @param int|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $addDefaultStore = 1)
    {
        if (!$this->getFlag('store_filter_added')) {
            $default_store = Store::DEFAULT_STORE_ID;
            if ($store instanceof Store) {
                $store = [$store->getId()];
            }

            if (!is_array($store)) {
                $store = [$store];
            }

            if (!empty($addDefaultStore)) {
                $store[] = $default_store;
            }

            $this->addFilter('store_id', ['in' => $store], 'public');
            $store_id = max($store);
            $q = "IF(main_table.code IN("
            . "SELECT c.code FROM kozeta_currency_coin c "
            . "INNER JOIN kozeta_currency_coin_store s ON c.coin_id = s.coin_id "
            . "WHERE s.store_id = $store_id), $store_id, $default_store)";

            $this->addFilter('store_id = '. $q, 1);
        }
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     * @SuppressWarnings(PHPMD.Ecg.Sql.SlowQuery)
     */
    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store_id')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable('kozeta_currency_coin_store')],
                'main_table.coin_id = store_table.coin_id',
                []
            )
            // @codingStandardsIgnoreStart
            ->group('main_table.coin_id');
            // @codingStandardsIgnoreEnd
        }
        parent::_renderFiltersBefore();
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Zend_Db_Select::GROUP);
        return $countSelect;
    }

    /**
     * @param $tableName
     * @param $linkField
     */
    protected function performAfterLoad($tableName, $linkField)
    {
        $linkedIds = $this->getColumnValues($linkField);
        if (!empty($linkedIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['kozeta_currency_coin_store' => $this->getTable($tableName)])
                ->where('kozeta_currency_coin_store.' . $linkField . ' IN (?)', $linkedIds);
            // @codingStandardsIgnoreStart
            $result = $connection->fetchAll($select);
            // @codingStandardsIgnoreEnd
            if ($result) {
                $storesData = [];
                foreach ($result as $storeData) {
                    $storesData[$storeData[$linkField]][] = $storeData['store_id'];
                }

                foreach ($this as $item) {
                    $linkedId = $item->getData($linkField);
                    if (!isset($storesData[$linkedId])) {
                        continue;
                    }
                    $storeIdKey = array_search(Store::DEFAULT_STORE_ID, $storesData[$linkedId], true);
                    if ($storeIdKey !== false) {
                        $stores = $this->storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                        $storeCode = key($stores);
                    } else {
                        $storeId = current($storesData[$linkedId]);
                        $storeCode = $this->storeManager->getStore($storeId)->getCode();
                    }
                    $item->setData('store_id', $storesData[$linkedId]);
                }
            }
        }
    }
}
