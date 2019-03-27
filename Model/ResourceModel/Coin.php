<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime as LibDateTime;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Kozeta\Currency\Model\Coin as CoinModel;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;

class Coin extends AbstractDb
{
    /**
     * Store model
     *
     * @var \Magento\Store\Model\Store
     */
    protected $store = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @param Context $context
     * @param DateTime $date
     * @param StoreManagerInterface $storeManager
     * @param LibDateTime $dateTime
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Context $context,
        DateTime $date,
        StoreManagerInterface $storeManager,
        LibDateTime $dateTime,
        ManagerInterface $eventManager
    ) {
        $this->date             = $date;
        $this->storeManager     = $storeManager;
        $this->dateTime         = $dateTime;
        $this->eventManager     = $eventManager;

        parent::__construct($context);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('kozeta_currency_coin', 'coin_id');
    }

    /**
     * Process coin data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $condition = ['coin_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('kozeta_currency_coin_store'), $condition);
        return parent::_beforeDelete($object);
    }

    /**
     * before save callback
     *
     * @param AbstractModel|\Kozeta\Currency\Model\Coin $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
//        $object->setUpdatedAt($this->date->gmtDate());
        $urlKey = $object->getData('url_key');
        if ($urlKey == '') {
            $urlKey = $object->getName();
        }
        $urlKey = $object->formatUrlKey($urlKey);
        $object->setUrlKey($urlKey);
        $validKey = false;
        
        while (!$validKey) {
            if ($this->getIsUniqueUrlKeyToStores($object)) {
                $validKey = true;
            } else {
                $urlKey = $this->generateNewUrlKey($urlKey);
                $object->setData('url_key', $urlKey);
            }
        }

        if (!$this->getIsUniqueCodeToStore($object)) {
            throw new LocalizedException(
                __('Currency code must be unique for given Store View.')
            );
        }
//https://magento.stackexchange.com/questions/178617/magento2-how-to-save-unique-values-for-my-custom-admin-module
        return parent::_beforeSave($object);
    }

    /**
     * @param $urlKey
     * @return string
     */
    protected function generateNewUrlKey($urlKey)
    {
        $parts = explode('-', $urlKey);
        $last = $parts[count($parts) - 1];
        if (!is_numeric($last)) {
            $urlKey = $urlKey.'-1';
        } else {
            $suffix = '-'.($last + 1);
            unset($parts[count($parts) - 1]);
            $urlKey = implode('-', $parts).$suffix;
        }
        return $urlKey;
    }

    /**
     * Assign coin to store views
     *
     * @param AbstractModel|\Kozeta\Currency\Model\Coin $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->saveStoreRelation($object);
        return parent::_afterSave($object);
    }

    /**
     * Perform operations after object load
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
        }
        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Kozeta\Currency\Model\Coin $object
     * @return \Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $storeIds = [
                Store::DEFAULT_STORE_ID,
                (int)$object->getStoreId()
            ];
            $select->join(
                [
                    'kozeta_currency_coin_store' => $this->getTable('kozeta_currency_coin_store')
                ],
                $this->getMainTable() . '.coin_id = kozeta_currency_coin_store.coin_id',
                []
            )
                ->where(
                    'kozeta_currency_coin_store.store_id IN (?)',
                    $storeIds
                )
                ->order('kozeta_currency_coin_store.store_id DESC')
                ->limit(1);
        }
        return $select;
    }

    /**
     * Retrieve load select with filter by url_key, store and activity
     *
     * @param string $urlKey
     * @param int|array $store
     * @param int $isActive
     * @return \Magento\Framework\DB\Select
     */
    protected function getLoadByUrlKeySelect($urlKey, $store, $isActive = null)
    {
        $select = $this->getConnection()
            ->select()
            ->from(['coin' => $this->getMainTable()])
            ->join(
                ['coin_store' => $this->getTable('kozeta_currency_coin_store')],
                'coin.coin_id = coin_store.coin_id',
                []
            )
            ->where(
                'coin.url_key = ?',
                $urlKey
            )
            ->where(
                'coin_store.store_id IN (?)',
                $store
            );
        if (!is_null($isActive)) {
            $select->where('coin.is_active = ?', $isActive);
        }
        return $select;
    }

    /**
     * Retrieve load select with filter by code and store
     *
     * It's prohibited to have currency code in default store view and any other store view at the same time.
     *
     * @param string $code
     * @param int|array $store
     * @param int $isActive
     * @return \Magento\Framework\DB\Select
     */
    protected function getLoadByCodeSelect($code, $store)
    {
//         $storeCondition = '1';
//         if ($store[0] != Store::DEFAULT_STORE_ID) {
//          $storeCondition = 'coin_store.store_id IN (?) OR coin_store.store_id = ' . Store::DEFAULT_STORE_ID;
//         }
        $storeCondition = 'coin_store.store_id IN (?)';
        $select = $this->getConnection()
            ->select()
            ->from(['coin' => $this->getMainTable()])
            ->join(
                ['coin_store' => $this->getTable('kozeta_currency_coin_store')],
                'coin.coin_id = coin_store.coin_id',
                []
            )
            ->where(
                'coin.code = ?',
                $code
            )
            ->where(
                $storeCondition,
                $store
            );
        return $select;
    }

    /**
     * Check if coin url_key exist
     * return coin id if coin exists
     *
     * @param string $urlKey
     * @param int $storeId
     * @return int
     */
    public function checkUrlKey($urlKey, $storeId)
    {
        $stores = [Store::DEFAULT_STORE_ID, $storeId];
        $select = $this->getLoadByUrlKeySelect($urlKey, $stores, 1);
        $select->reset(\Zend_Db_Select::COLUMNS)
            ->columns('coin.coin_id')
            ->order('coin_store.store_id DESC')
            ->limit(1);
        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $coinId
     * @return array
     */
    public function lookupStoreIds($coinId)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()->from(
            $this->getTable('kozeta_currency_coin_store'),
            'store_id'
        )->where(
            'coin_id = ?',
            (int)$coinId
        );
        return $adapter->fetchCol($select);
    }

    /**
     * Set store model
     *
     * @param Store $store
     * @return $this
     */
    public function setStore(Store $store)
    {
        $this->store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore($this->store);
    }

    /**
     * check if url key is unique
     *
     * @param AbstractModel|\Kozeta\Currency\Model\Coin $object
     * @return bool
     */
    public function getIsUniqueUrlKeyToStores(AbstractModel $object)
    {
        if ($this->storeManager->hasSingleStore() || !$object->hasStores()) {
            $stores = [Store::DEFAULT_STORE_ID];
        } else {
            $stores = (array)$object->getData('store_id');
        }
        $select = $this->getLoadByUrlKeySelect($object->getData('url_key'), $stores);
        if ($object->getId()) {
            $select->where('coin_store.coin_id <> ?', $object->getId());
        }
        if ($this->getConnection()->fetchRow($select)) {
            return false;
        }
        return true;
    }

    /**
     * check if url key is unique
     *
     * @param AbstractModel|\Kozeta\Currency\Model\Coin $object
     * @return bool
     */
    public function getIsUniqueCodeToStore(AbstractModel $object)
    {
        if ($this->storeManager->hasSingleStore()) {
            $stores = [Store::DEFAULT_STORE_ID];
        } else {
            $stores = (array)$object->getData('store_id');
        }
        $select = $this->getLoadByCodeSelect($object->getData('code'), $stores);
        if ($object->getId()) {
            $select->where('coin_store.coin_id <> ?', $object->getId());
        }
        if ($this->getConnection()->fetchRow($select)) {
            return false;
        }
        return true;
    }
    
    /**
     * @param CoinModel $coin
     * @return $this
     */
    protected function saveStoreRelation(CoinModel $coin)
    {
        $oldStores = $this->lookupStoreIds($coin->getId());
        $newStores = (array)$coin->getStoreId();
        if (empty($newStores)) {
            $newStores = (array)$coin->getStoreId();
        }
        $table = $this->getTable('kozeta_currency_coin_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = [
                'coin_id = ?' => (int)$coin->getId(),
                'store_id IN (?)' => $delete
            ];
            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = [
                    'coin_id' => (int)$coin->getId(),
                    'store_id' => (int)$storeId
                ];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }
        return $this;
    }

    /**
     * @param AbstractModel $object
     * @param $attribute
     * @return $this
     * @throws \Exception
     */
    public function saveAttribute(AbstractModel $object, $attribute)
    {
        if (is_string($attribute)) {
            $attributes = [$attribute];
        } else {
            $attributes = $attribute;
        }
        if (is_array($attributes) && !empty($attributes)) {
            $this->getConnection()->beginTransaction();
            $data = array_intersect_key($object->getData(), array_flip($attributes));
            try {
                $this->beforeSaveAttribute($object, $attributes);
                if ($object->getId() && !empty($data)) {
                    $this->getConnection()->update(
                        $object->getResource()->getMainTable(),
                        $data,
                        [$object->getResource()->getIdFieldName() . '= ?' => (int)$object->getId()]
                    );
                    $object->addData($data);
                }
                $this->afterSaveAttribute($object, $attributes);
                $this->getConnection()->commit();
            } catch (\Exception $e) {
                $this->getConnection()->rollBack();
                throw $e;
            }
        }
        return $this;
    }

    /**
     * @param AbstractModel $object
     * @param $attribute
     * @return $this
     */
    protected function beforeSaveAttribute(AbstractModel $object, $attribute)
    {
        if ($object->getEventObject() && $object->getEventPrefix()) {
            $this->eventManager->dispatch(
                $object->getEventPrefix() . '_save_attribute_before',
                [
                    $object->getEventObject() => $this,
                    'object' => $object,
                    'attribute' => $attribute
                ]
            );
        }
        return $this;
    }

    /**
     * After save object attribute
     *
     * @param AbstractModel $object
     * @param string $attribute
     * @return \Magento\Sales\Model\ResourceModel\Attribute
     */
    protected function afterSaveAttribute(AbstractModel $object, $attribute)
    {
        if ($object->getEventObject() && $object->getEventPrefix()) {
            $this->eventManager->dispatch(
                $object->getEventPrefix() . '_save_attribute_after',
                [
                    $object->getEventObject() => $this,
                    'object' => $object,
                    'attribute' => $attribute
                ]
            );
        }
        return $this;
    }
}
