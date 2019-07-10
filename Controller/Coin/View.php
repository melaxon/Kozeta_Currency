<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Controller\Coin;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Kozeta\Currency\Api\CoinRepositoryInterface;
use Kozeta\Currency\Model\Coin\Url as UrlModel;

class View extends Action
{
    /**
     * @var string
     */
    const BREADCRUMBS_CONFIG_PATH = 'kozeta_currency/coin/breadcrumbs';
    /**
     * @var \Kozeta\Currency\Api\CoinRepositoryInterface
     */
    protected $coinRepository;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Kozeta\Currency\Model\Coin\Url
     */
    protected $urlModel;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Context $context
     * @param CoinRepositoryInterface $coinRepository
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param UrlModel $urlModel
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        CoinRepositoryInterface $coinRepository,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        UrlModel $urlModel,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->coinRepository       = $coinRepository;
        $this->resultPageFactory    = $resultPageFactory;
        $this->coreRegistry         = $coreRegistry;
        $this->urlModel             = $urlModel;
        $this->scopeConfig          = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Displays coin details
     *
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\View\Result\Page
     * @throws \Exception
     */
    public function execute()
    {

        $coinId = (int)$this->getRequest()->getParam('id');
        $coin = $this->coinRepository->getById($coinId);

        if (!$coin->getIsActive()) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }

        $this->coreRegistry->register('current_coin', $coin);
        $resultPage = $this->resultPageFactory->create();
        $title = trim($coin->getMetaTitle()) != '' ? $coin->getMetaTitle() : $coin->getName();
        $resultPage->getConfig()->getTitle()->set($title);
        $resultPage->getConfig()->setDescription($coin->getMetaDescription());
        $resultPage->getConfig()->setKeywords($coin->getMetaKeywords());
        if ($this->scopeConfig->isSetFlag(self::BREADCRUMBS_CONFIG_PATH, ScopeInterface::SCOPE_STORE)) {
            /** @var \Magento\Theme\Block\Html\Breadcrumbs $breadcrumbsBlock */
            $breadcrumbsBlock = $resultPage->getLayout()->getBlock('breadcrumbs');
            if ($breadcrumbsBlock) {
                $breadcrumbsBlock->addCrumb(
                    'home',
                    [
                        'label' => __('Home'),
                        'link'  => $this->_url->getUrl('')
                    ]
                );
//                 $breadcrumbsBlock->addCrumb(
//                     'coins',
//                     [
//                         'label' => __('Coins'),
//                         'link'  => $this->urlModel->getListUrl()
//                     ]
//                 );
                $breadcrumbsBlock->addCrumb(
                    'coin-'.$coin->getId(),
                    [
                        'label' => $coin->getName()
                    ]
                );
            }
        }

        return $resultPage;
    }
}
