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
     * @param scopeConfig $scopeConfig
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $context,
        array $data = []
    ) {

        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (empty(trim($this->getLabel())) || empty(trim($this->getHref()))) {
            return '';
        }
        return parent::_toHtml();
    }
    
    /**
     * @return string
     */
    public function getHref()
    {
        return '../../../../../'. $this->scopeConfig->getValue(
            self::COINS_LIST_URL_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->scopeConfig->getValue(
            self::TOP_LINKS_TITLE_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }
}
