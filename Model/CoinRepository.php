<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Kozeta\Currency\Api\CoinRepositoryInterface;
use Kozeta\Currency\Api\Data;
use Kozeta\Currency\Api\Data\CoinInterface;
use Kozeta\Currency\Api\Data\CoinInterfaceFactory;
use Kozeta\Currency\Api\Data\CoinSearchResultsInterfaceFactory;
use Kozeta\Currency\Model\ResourceModel\Coin as ResourceCoin;
use Kozeta\Currency\Model\ResourceModel\Coin\Collection;
use Kozeta\Currency\Model\ResourceModel\Coin\CollectionFactory as CoinCollectionFactory;

/**
 * Class CoinRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CoinRepository implements CoinRepositoryInterface
{
    /**
     * @var array
     */
    protected $instances = [];
    /**
     * @var ResourceCoin
     */
    protected $resource;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var CoinCollectionFactory
     */
    protected $coinCollectionFactory;
    /**
     * @var CoinSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;
    /**
     * @var CoinInterfaceFactory
     */
    protected $coinInterfaceFactory;
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    public function __construct(
        ResourceCoin $resource,
        StoreManagerInterface $storeManager,
        CoinCollectionFactory $coinCollectionFactory,
        CoinSearchResultsInterfaceFactory $coinSearchResultsInterfaceFactory,
        CoinInterfaceFactory $coinInterfaceFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->resource                 = $resource;
        $this->storeManager             = $storeManager;
        $this->coinCollectionFactory  = $coinCollectionFactory;
        $this->searchResultsFactory     = $coinSearchResultsInterfaceFactory;
        $this->coinInterfaceFactory   = $coinInterfaceFactory;
        $this->dataObjectHelper         = $dataObjectHelper;
    }
    /**
     * Save page.
     *
     * @param \Kozeta\Currency\Api\Data\CoinInterface $coin
     * @return \Kozeta\Currency\Api\Data\CoinInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(CoinInterface $coin)
    {
        /** @var CoinInterface|\Magento\Framework\Model\AbstractModel $coin */
        if (empty($coin->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $coin->setStoreId($storeId);
        }
        try {
            $this->resource->save($coin);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the coin: %1',
                $exception->getMessage()
            ));
        }
        return $coin;
    }

    /**
     * Retrieve Coin.
     *
     * @param int $coinId
     * @return \Kozeta\Currency\Api\Data\CoinInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($coinId)
    {
        if (!isset($this->instances[$coinId])) {
            /** @var \Kozeta\Currency\Api\Data\CoinInterface|\Magento\Framework\Model\AbstractModel $coin */
            $coin = $this->coinInterfaceFactory->create();
            $this->resource->load($coin, $coinId);
            if (!$coin->getId()) {
                throw new NoSuchEntityException(__('Requested coin doesn\'t exist'));
            }
            $this->instances[$coinId] = $coin;
        }
        return $this->instances[$coinId];
    }

    /**
     * Retrieve pages matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Kozeta\Currency\Api\Data\CoinSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Kozeta\Currency\Api\Data\CoinSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Kozeta\Currency\Model\ResourceModel\Coin\Collection $collection */
        $collection = $this->coinCollectionFactory->create();

        //Add filters from root filter group to the collection
        /** @var FilterGroup $group */
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $sortOrders = $searchCriteria->getSortOrders();
        /** @var SortOrder $sortOrder */
        if ($sortOrders) {
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $field = $sortOrder->getField();
                $collection->addOrder(
                    $field,
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        } else {
            // set a default sorting order since this method is used constantly in many
            // different blocks
            $field = 'coin_id';
            $collection->addOrder($field, 'ASC');
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var \Kozeta\Currency\Api\Data\CoinInterface[] $coins */
        $coins = [];
        /** @var \Kozeta\Currency\Model\Coin $coin */
        foreach ($collection as $coin) {
            /** @var \Kozeta\Currency\Api\Data\CoinInterface $coinDataObject */
            $coinDataObject = $this->coinInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray($coinDataObject, $coin->getData(), CoinInterface::class);
            $coins[] = $coinDataObject;
        }
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($coins);
    }

    /**
     * Delete coin.
     *
     * @param \Kozeta\Currency\Api\Data\CoinInterface $coin
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(CoinInterface $coin)
    {
        /** @var \Kozeta\Currency\Api\Data\CoinInterface|\Magento\Framework\Model\AbstractModel $coin */
        $id = $coin->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($coin);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new StateException(
                __('Unable to remove coin %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * Delete coin by ID.
     *
     * @param int $coinId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($coinId)
    {
        $coin = $this->getById($coinId);
        return $this->delete($coin);
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $collection)
    {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
        return $this;
    }
}
