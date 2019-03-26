<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Result\PageFactory;
use Kozeta\Currency\Api\CoinRepositoryInterface;

abstract class Coin extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Kozeta_Currency::currency_manage';

    /**
     * @var string
     */
    const ACTION_RESOURCE = 'Kozeta_Currency::coin';
    /**
     * coin factory
     *
     * @var CoinRepositoryInterface
     */
    protected $coinRepository;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * date filter
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Registry $registry
     * @param CoinRepositoryInterface $coinRepository
     * @param PageFactory $resultPageFactory
     * @param Date $dateFilter
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        CoinRepositoryInterface $coinRepository,
        PageFactory $resultPageFactory,
        Date $dateFilter,
        Context $context
    ) {
        $this->coreRegistry      = $registry;
        $this->coinRepository  = $coinRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->dateFilter        = $dateFilter;
        parent::__construct($context);
    }

    /**
     * filter dates
     *
     * @param array $data
     * @return array
     */
    public function filterData($data)
    {
//        $inputFilter = new \Zend_Filter_Input(
//            ['code' => $this->dateFilter],
//            [],
//            $data
//        );
//        $data = $inputFilter->getUnescaped();
//        if (isset($data['something_'])) {
//            if (is_array($data['something_'])) {
//                $data['something_'] = implode(',', $data['something_']);
//            }
//        }
        return $data;
    }
}
