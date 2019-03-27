<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Ui\DataProvider\Coin\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Kozeta\Currency\Model\ResourceModel\Coin\CollectionFactory;

/*
 * UI Data Provider
 *
 */
class CoinData implements ModifierInterface
{
    /**
     * @var \Kozeta\Currency\Model\ResourceModel\Coin\Collection
     */
    protected $_collection;

    /**
     * @param CollectionFactory $coinFactory
     */
    public function __construct(
        CollectionFactory $coinFactory
    ) {
        $this->_collection = $coinFactory->create();
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    /**
     * @param array $data
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function modifyData(array $data)
    {
        $items = $this->_collection->getItems();
        /** @var \Kozeta\Currency\Model\Coin $coin */
        foreach ($items as $coin) {
            $_data = $coin->getData();
            if (isset($_data['avatar'])) {
                $avatar = [];
                $avatar[0]['name'] = $coin->getAvatar();
                $avatar[0]['url'] = $coin->getAvatarUrl();
                $_data['avatar'] = $avatar;
            }
            $coin->setData($_data);
            $data[$coin->getId()] = $_data;
        }
        return $data;
    }
}
