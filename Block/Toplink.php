<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Block;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Add a link to top menu
 *
 */
class Toplink extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var string
     */
    const TOP_LINKS_TITLE_CONFIG_PATH = 'currency/coin/top_links_title';

    /**
     * @var string
     */
    const COINS_LIST_URL_CONFIG_PATH = 'currency/coin/list_url';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var Top link label
     */
    protected $title;

    /**
     * @var Top link url
     */
    protected $url;

    /**
     * @param scopeConfig $scopeConfig
     * @param Context $context
     */
    public function __construct(

        ScopeConfigInterface $scopeConfig,
		Context $context,
		array $data = []
    ) {

        $this->scopeConfig = $scopeConfig;
        $this->title = $this->scopeConfig->getValue(
            self::TOP_LINKS_TITLE_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
        $this->url = $this->scopeConfig->getValue(
            self::COINS_LIST_URL_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
        
		parent::__construct($context, $data);
    }

	/**
	* Render block HTML.
	*
	* @return string
	*/
	protected function _toHtml()
    {
    	if (empty(trim($this->title)) || empty(trim($this->url))) return '';
    	return parent::_toHtml();
    }
    
    /**
     * @return string
     */
    public function getHref()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->title;
    }
}
