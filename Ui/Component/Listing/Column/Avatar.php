<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Kozeta\Currency\Model\Uploader;
use Kozeta\Currency\Block\Adminhtml\Coin\Img;

/**
 * @method Avatar setName($name)
 */
class Avatar extends Column
{
    const ALT_FIELD = 'name';

    /**
     * @var \Kozeta\Currency\Model\Uploader
     */
    private $imageModel;

    /**
     * @var Kozeta\Currency\Block\Adminhtml\Coin\Img
     */
    private $img;
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param \Kozeta\Currency\Model\Uploader $imageModel
     * @param array $components
     * @param array $data
     * @param Img $img
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Uploader $imageModel,
        Img $img,
        array $components = [],
        array $data = []
    ) {
        $this->imageModel = $imageModel;
        $this->urlBuilder = $urlBuilder;
        $this->img = $img;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $url = '';
                if ($item[$fieldName] != '') {
                    $url = $this->imageModel->getBaseUrl().$this->imageModel->getBasePath().$item[$fieldName];
                } else {
                    $url = $this->img->getPlaceholderUrl();
                }
                $item[$fieldName . '_src'] = $url;
                $item[$fieldName . '_alt'] = $this->getAlt($item) ?: '';
                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    'kozeta_currency/coin/edit',
                    ['coin_id' => $item['coin_id']]
                );
                $item[$fieldName . '_orig_src'] = $url;
            }
        }

        return $dataSource;
    }

    /**
     * @param array $row
     *
     * @return null|string
     */
    private function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }
}
