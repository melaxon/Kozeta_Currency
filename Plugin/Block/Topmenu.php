<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Plugin\Block;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Tree\Node;
use Magento\Theme\Block\Html\Topmenu as TopmenuBlock;
use Kozeta\Currency\Model\Coin\Url;

class Topmenu
{
    /**
     * @var string
     */
    const TOP_MENU_TITLE_CONFIG_PATH = 'currency/coin/top_menu_title';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Url
     */
    protected $url;
    /**
     * @var Http
     */
    protected $request;

    /**
     * @param Url $url
     * @param Http $request
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Url $url,
        Http $request,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->url      = $url;
        $this->request  = $request;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param TopmenuBlock $subject
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @SuppressWarnings("PMD.UnusedFormalParameter")
     */
    // @codingStandardsIgnoreStart
    public function beforeGetHtml(
        TopmenuBlock $subject,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    ) {
        
        $title = $this->scopeConfig->getValue(
            self::TOP_MENU_TITLE_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
		if (empty(trim($title)))
		{
			return;
		}
        
        // @codingStandardsIgnoreEnd
        $node = new Node(
            $this->getNodeAsArray(),
            'id',
            $subject->getMenu()->getTree(),
            $subject->getMenu()
        );
        
        

        $subject->getMenu()->addChild($node);
    }

    /**
     * @return array
     */
    protected function getNodeAsArray()
    {

        $title = $this->scopeConfig->getValue(
            self::TOP_MENU_TITLE_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );

        return [
            'name' => $title,
            'id' => 'coins-node',
            'url' => $this->url->getListUrl(),
            'has_active' => false,
            'is_active' => in_array($this->request->getFullActionName(), $this->getActiveHandles()),
            'sortOrder' => -1
        ];
    }

    /**
     * @return array
     */
    protected function getActiveHandles()
    {
        return [
            'currency_coin_index',
            'currency_coin_view'
        ];
    }
}
