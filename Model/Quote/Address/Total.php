<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */
namespace Kozeta\Currency\Model\Quote\Address;

use Kozeta\Currency\Model\Precision;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Total
 *
 * @method string getCode()
 *
 * @api
 * @since 100.0.2
 */
class Total extends \Magento\Quote\Model\Quote\Address\Total
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var string
     */
    protected $baseCurrencyCode;

    /**
     * @var \Kozeta\Currency\Model\Precision
     */
    protected $precisionObject;

    /**
     * Constructor
     *
     * @param \Kozeta\Currency\Model\Precision $precisionObject
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Precision $precisionObject,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->precisionObject = $precisionObject;
        $this->storeManager = $storeManager;
        parent::__construct($data);
    }

    /**
     * Set total amount value
     *
     * @param string $code
     * @param float $amount
     * @return $this
     */
    public function setTotalAmount($code, $amount)
    {
        $precision = (int) $this->precisionObject->getPrecision();
        
        $amount = is_float($amount) ? round($amount, $precision + 2) : $amount;

        $this->totalAmounts[$code] = $amount;
        if ($code != 'subtotal') {
            $code = $code . '_amount';
        }
        $this->setData($code, $amount);

        return $this;
    }

    /**
     * Set total amount value in base store currency
     *
     * @param string $code
     * @param float $amount
     * @return $this
     */
    public function setBaseTotalAmount($code, $amount)
    {
        $baseCurrency = $this->getBaseCurrencyCode();
        $precision = (int) $this->precisionObject->getPrecisionByCode($baseCurrency);
     
        $amount = is_float($amount) ? round($amount, $precision + 2) : $amount;

        $this->baseTotalAmounts[$code] = $amount;
        if ($code != 'subtotal') {
            $code = $code . '_amount';
        }
        $this->setData('base_' . $code, $amount);

        return $this;
    }

    /**
     * @return Base currency code
     */
    public function getBaseCurrencyCode()
    {
        if (is_string($this->baseCurrencyCode)) {
            return $this->baseCurrencyCode;
        }
        $this->baseCurrencyCode = $this->storeManager->getStore()->getBaseCurrencyCode();
        return $this->baseCurrencyCode;
    }
}
