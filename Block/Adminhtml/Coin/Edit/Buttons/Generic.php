<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Block\Adminhtml\Coin\Edit\Buttons;

use Magento\Backend\Block\Widget\Context;
use Kozeta\Currency\Api\CoinRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Generic
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var CoinRepositoryInterface
     */
    private $coinRepository;

    /**
     * @param Context $context
     * @param CoinRepositoryInterface $coinRepository
     */
    public function __construct(
        Context $context,
        CoinRepositoryInterface $coinRepository
    ) {
        $this->context = $context;
        $this->coinRepository = $coinRepository;
    }

    /**
     * Return Coin page ID
     *
     * @return int|null
     */
    public function getCoinId()
    {
        try {
            return $this->coinRepository->getById(
                $this->context->getRequest()->getParam('coin_id')
            )->getId();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
