<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */
namespace Kozeta\Currency\Plugin\Precision\Framework\Pricing;

class PriceCurrency
{

    /**
     * @var \Kozeta\Currency\Model\Precision
     */
    protected $precisionObject;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    protected $messageManager;

    public function __construct(
        \Kozeta\Currency\Model\Precision $precisionObject,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->precisionObject = $precisionObject;
        $this->_storeManager = $storeManager;
        $this->messageManager = $context->getMessageManager();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeFormat(\Magento\Directory\Model\PriceCurrency $subject, ...$args)
    {
        if (!empty($arg[4])) {
            $store = $this->_storeManager->getStore()->getId();
            $args[2] = $this->precisionObject->getPrecisionByCode($arg[4], $store);
        } else {
            $args[2] = $this->precisionObject->getPrecision();
        }
        return $args;
    }

    /**
     * @param \Magento\Directory\Model\PriceCurrency $subject
     * @param callable $proceed
     * @param $price
     * @param array ...$args
     * @return float
     * TO DO:
     * - Consider to separate the precision of base currency and default currency ....
     * - Locate the bug where Magento rounds the price before convertion and then rounds it again (affected: cart and minicart item price)
     * - Replace hardcoded precisions wherever it is possible
     */
    public function aroundRound(\Magento\Directory\Model\PriceCurrency $subject, callable $proceed, $price, ...$args)
    {
        $precision = $this->precisionObject->getPrecision();
        if ($precision < $subject::DEFAULT_PRECISION) {
            return $proceed($price);
        }
        $roundedPricce = round($price, $this->precisionObject->getPrecision());
        return $roundedPricce;
    }

    /**
     * @param \Magento\Directory\Model\PriceCurrency $subject
     * @param array ...$args
     * @return array
     */
    public function beforeConvertAndFormat(\Magento\Directory\Model\PriceCurrency $subject, ...$args)
    {
        $args[1] = isset($args[1])? $args[1] : null;
        $args[2] = $this->precisionObject->getPrecision();
        return $args;
    }

    /**
     * @param \Magento\Directory\Model\PriceCurrency $subject
     * @param array ...$args
     * @return array
     */
    public function beforeConvertAndRound(\Magento\Directory\Model\PriceCurrency $subject, ...$args)
    {
        $args[1] = isset($args[1])? $args[1] : null;
        $args[2] = isset($args[2])? $args[2] : null;
        $args[3] = $this->precisionObject->getPrecision();
        return $args;
    }
}
