<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Kozeta\Currency\Api\Data\CoinInterface;

/**
 * @api
 */
interface CoinRepositoryInterface
{
    /**
     * Save page.
     *
     * @param CoinInterface $coin
     * @return CoinInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(CoinInterface $coin);

    /**
     * Retrieve Coin.
     *
     * @param int $coinId
     * @return CoinInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($coinId);

    /**
     * Retrieve pages matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Kozeta\Currency\Api\Data\CoinSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete coin.
     *
     * @param CoinInterface $coin
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(CoinInterface $coin);

    /**
     * Delete coin by ID.
     *
     * @param int $coinId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($coinId);
}
