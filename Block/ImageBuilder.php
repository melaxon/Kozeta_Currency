<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Block;

use Magento\Framework\Model\AbstractModel;
use Kozeta\Currency\Helper\Image as ImageHelper;
use Kozeta\Currency\Helper\ImageFactory as HelperFactory;

class ImageBuilder
{
    /**
     * @var ImageFactory
     */
    protected $imageFactory;

    /**
     * @var HelperFactory
     */
    protected $helperFactory;

    /**
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $entity;

    /**
     * @var string
     */
    protected $imageId;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var string
     */
    protected $entityCode;

    /**
     * @param HelperFactory $helperFactory
     * @param ImageFactory $imageFactory
     * @param $entityCode
     */
    public function __construct(
        HelperFactory $helperFactory,
        ImageFactory $imageFactory,
        $entityCode
    ) {
        $this->helperFactory = $helperFactory;
        $this->imageFactory  = $imageFactory;
        $this->entityCode    = $entityCode;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return $this
     */
    public function setEntity(AbstractModel $entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * Set image ID
     *
     * @param string $imageId
     * @return $this
     */
    public function setImageId($imageId)
    {
        $this->imageId = $imageId;
        return $this;
    }

    /**
     * Set custom attributes
     *
     * @param array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        if ($attributes) {
            $this->attributes = $attributes;
        }
        return $this;
    }

    /**
     * Retrieve image custom attributes for HTML element
     *
     * @return string
     */
    protected function getCustomAttributes()
    {
        $result = [];
        foreach ($this->attributes as $name => $value) {
            $result[] = $name . '="' . $value . '"';
        }
        return !empty($result) ? implode(' ', $result) : '';
    }

    /**
     * Calculate image ratio
     *
     * @param ImageHelper $helper
     * @return float|int
     */
    protected function getRatio(ImageHelper $helper)
    {
        $width = $helper->getWidth();
        $height = $helper->getHeight();
        if ($width && $height) {
            return $height / $width;
        }
        return 1;
    }

    /**
     * Create image block
     *
     * @return \Kozeta\Currency\Block\Image
     */
    public function create()
    {
        /** @var ImageHelper $helper */
        $helper = $this->helperFactory
            ->create([
                'entityCode' => $this->entityCode
            ])
            ->init(
                $this->entity,
                $this->imageId,
                $this->attributes
            );

        $template = $helper->getFrame()
            ? 'Kozeta_Currency::image.phtml'
            : 'Kozeta_Currency::image_with_borders.phtml';

        $imagesize = $helper->getResizedImageInfo();

        $data = [
            'data' => [
                'template'              => $template,
                'image_url'             => $helper->getUrl(),
                'width'                 => $helper->getWidth(),
                'height'                => $helper->getHeight(),
                'label'                 => $helper->getLabel(),
                'ratio'                 => $this->getRatio($helper),
                'custom_attributes'     => $this->getCustomAttributes(),
                'resized_image_width'   => !empty($imagesize[0]) ? $imagesize[0] : $helper->getWidth(),
                'resized_image_height'  => !empty($imagesize[1]) ? $imagesize[1] : $helper->getHeight(),
            ],
        ];

        return $this->imageFactory->create($data);
    }
}
