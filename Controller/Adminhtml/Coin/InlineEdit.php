<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Controller\Adminhtml\Coin;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\CurrencySymbol\Model\System\Currencysymbol;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Result\PageFactory;
use Kozeta\Currency\Api\CoinRepositoryInterface;
use Kozeta\Currency\Api\Data\CoinInterface;
use Kozeta\Currency\Api\Data\CoinInterfaceFactory;
use Kozeta\Currency\Controller\Adminhtml\Coin as CoinController;
use Kozeta\Currency\Model\Coin;
use Kozeta\Currency\Model\ResourceModel\Coin as CoinResourceModel;

class InlineEdit extends CoinController
{
    /**
     * @var DataObjectHelper
     */
    protected $_dataObjectHelper;
    
    /**
     * @var Currencysymbol
     */
    protected $_currencySymbol;
    
    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;
    /**
     * @var JsonFactory
     */
    protected $_jsonFactory;
    /**
     * @var CoinResourceModel
     */
    protected $_coinResourceModel;

    /**
     * @param Registry $registry
     * @param CoinRepositoryInterface $coinRepository
     * @param PageFactory $resultPageFactory
     * @param Date $dateFilter
     * @param Context $context
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param JsonFactory $jsonFactory
     * @param CoinResourceModel $coinResourceModel
     * @param Currencysymbol $currencySymbol
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Registry $registry,
        CoinRepositoryInterface $coinRepository,
        PageFactory $resultPageFactory,
        Date $dateFilter,
        Context $context,
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        JsonFactory $jsonFactory,
        CoinResourceModel $coinResourceModel,
        Currencysymbol $currencySymbol
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->_dataObjectHelper    = $dataObjectHelper;
        $this->_jsonFactory         = $jsonFactory;
        $this->_coinResourceModel = $coinResourceModel;
        $this->_currencySymbol = $currencySymbol;
        parent::__construct($registry, $coinRepository, $resultPageFactory, $dateFilter, $context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->_jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && !empty($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $coinId) {
            /** @var \Kozeta\Currency\Model\Coin\CoinInterface $coin */
            $coin = $this->coinRepository->getById((int)$coinId);
            $code = $coin->getCode();
            
            try {
                $coinData = $this->filterData($postItems[$coinId]);
                $this->_dataObjectHelper->populateWithArray($coin, $coinData, CoinInterface::class);
                $this->_coinResourceModel->saveAttribute($coin, array_keys($coinData));
            } catch (LocalizedException $e) {
                $messages[] = $this->getErrorWithCoinId($coin, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithCoinId($coin, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithCoinId(
                    $coin,
                    __('Something went wrong while saving the coin.')
                        . " ($code). "
                        . $e->getMessage()
                );
                $error = true;
            }

            $symbol = $postItems[$coinId]['symbol'];
            $symbolsDataArray = [$code => $symbol];
            try {
                $this->_currencySymbol->setCurrencySymbolsData($symbolsDataArray);
                $this->messageManager->addSuccess(__('You applied the custom currency symbols.'));
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithCoinId(
                    $coin,
                    __('Something went wrong while saving the symbol.')
                        . " ($symbol). "
                        . $e->getMessage()
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add coin id to error message
     *
     * @param Coin $coin
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithCoinId(Coin $coin, $errorText)
    {
        return '[Coin ID: ' . $coin->getId() . '] ' . $errorText;
    }
}
