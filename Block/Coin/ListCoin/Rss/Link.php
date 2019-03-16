<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Block\Coin\ListCoin\Rss;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Kozeta\Currency\Model\Coin\Rss as RssModel;

class Link extends Template
{
    /**
     * @var RssModel
     */
    protected $rssModel;

    /**
     * @param RssModel $rssModel
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        RssModel $rssModel,
        array $data = []
    ) {
        $this->rssModel = $rssModel;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function isRssEnabled()
    {
        return $this->rssModel->isRssEnabled();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return __('Subscribe to RSS Feed');
    }
    /**
     * @return string
     */
    public function getLink()
    {
        return $this->rssModel->getRssLink();
    }
}
