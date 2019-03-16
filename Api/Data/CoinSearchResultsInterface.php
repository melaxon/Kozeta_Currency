<?php
namespace Kozeta\Currency\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface CoinSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get coin list.
     *
     * @return \Kozeta\Currency\Api\Data\CoinInterface[]
     */
    public function getItems();

    /**
     * Set coins list.
     *
     * @param \Kozeta\Currency\Api\Data\CoinInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
