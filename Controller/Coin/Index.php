<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Controller\Coin;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Index extends Action
{
    /**
     * @var string
     */
    const META_DESCRIPTION_CONFIG_PATH = 'currency/coin/meta_description';
    /**
     * @var string
     */
    const META_KEYWORDS_CONFIG_PATH = 'currency/coin/meta_keywords';
    /**
     * @var string
     */
    const META_TITLE_CONFIG_PATH = 'currency/coin/meta_title';
    /**
     * @var string
     */
//    const BREADCRUMBS_CONFIG_PATH = 'kozeta_currency/coin/breadcrumbs';

    /**
     * @var string
     */
	const TOP_MENU_TITLE_CONFIG_PATH = 'currency/coin/top_menu_title';

    /**
     * @var string
     */
	const TOP_LINKS_TITLE_CONFIG_PATH = 'currency/coin/top_links_title';
    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        ScopeConfigInterface $scopeConfig
    ) {

//        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $title = $this->scopeConfig->getValue(self::TOP_MENU_TITLE_CONFIG_PATH, ScopeInterface::SCOPE_STORE);
		
		if (
			empty($this->scopeConfig->getValue(self::TOP_MENU_TITLE_CONFIG_PATH, ScopeInterface::SCOPE_STORE)) &&
			empty($this->scopeConfig->getValue(self::TOP_LINKS_TITLE_CONFIG_PATH, ScopeInterface::SCOPE_STORE))
			//empty(trim($this->scopeConfig->getValue(self::TOP_LINKS_TITLE_CONFIG_PATH,ScopeInterface::SCOPE_STORE)
		)
		{
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
		}

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(
            $this->scopeConfig->getValue(self::META_TITLE_CONFIG_PATH, ScopeInterface::SCOPE_STORE)
        );
        $resultPage->getConfig()->setDescription(
            $this->scopeConfig->getValue(self::META_DESCRIPTION_CONFIG_PATH, ScopeInterface::SCOPE_STORE)
        );
        $resultPage->getConfig()->setKeywords(
            $this->scopeConfig->getValue(self::META_KEYWORDS_CONFIG_PATH, ScopeInterface::SCOPE_STORE)
        );
//        if ($this->scopeConfig->isSetFlag(self::BREADCRUMBS_CONFIG_PATH, ScopeInterface::SCOPE_STORE)) {
            /** @var \Magento\Theme\Block\Html\Breadcrumbs $breadcrumbsBlock */
            $breadcrumbsBlock = $resultPage->getLayout()->getBlock('breadcrumbs');
            if ($breadcrumbsBlock) {
                $breadcrumbsBlock->addCrumb(
                    'home',
                    [
                        'label'    => __('Home'),
                        'link'     => $this->_url->getUrl('')
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'coins',
                    [
                        'label'    => __('Coins'),
                    ]
                );
            }
        //}
        return $resultPage;
    }
}
