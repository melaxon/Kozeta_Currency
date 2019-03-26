<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Controller\Adminhtml\Coin;

use Magento\Backend\Model\Session;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Result\PageFactory;
use Kozeta\Currency\Api\CoinRepositoryInterface;
use Kozeta\Currency\Api\Data\CoinInterface;
use Kozeta\Currency\Api\Data\CoinInterfaceFactory;
use Kozeta\Currency\Controller\Adminhtml\Coin;
use Kozeta\Currency\Model\Uploader;
use Kozeta\Currency\Model\UploaderPool;

class Save extends Coin
{
    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var UploaderPool
     */
    protected $uploaderPool;

    /**
     * @param Registry $registry
     * @param CoinRepositoryInterface $coinRepository
     * @param PageFactory $resultPageFactory
     * @param Date $dateFilter
     * @param Context $context
     * @param CoinInterfaceFactory $coinFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param UploaderPool $uploaderPool
     */
    public function __construct(
        Registry $registry,
        CoinRepositoryInterface $coinRepository,
        PageFactory $resultPageFactory,
        Date $dateFilter,
        Context $context,
        CoinInterfaceFactory $coinFactory,
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        UploaderPool $uploaderPool
    ) {
        $this->coinFactory = $coinFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->uploaderPool = $uploaderPool;
        parent::__construct($registry, $coinRepository, $resultPageFactory, $dateFilter, $context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Kozeta\Currency\Api\Data\CoinInterface $coin */
        $coin = null;
        $data = $this->getRequest()->getPostValue();

        if (!isset($data['store_id'])) {
            $data['store_id'] = [ \Magento\Cms\Ui\Component\Listing\Column\Cms\Options::ALL_STORE_VIEWS ];
        }
        $id = !empty($data['coin_id']) ? $data['coin_id'] : null;
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
// Update existing coin; 
            if ($id) {
                $coin = $this->coinRepository->getById((int)$id);
//Create new coin; 
            } else {
                unset($data['coin_id']);
                $coin = $this->coinFactory->create();
            }
            $data['avatar'] = $this->getUploader('image')->uploadFileAndGetName('avatar', $data);
            $this->dataObjectHelper->populateWithArray($coin, $data, CoinInterface::class);
            $this->coinRepository->save($coin);
            $this->messageManager->addSuccessMessage(__('The currency was saved successfully.'));
            if ($this->getRequest()->getParam('back')) {
                $resultRedirect->setPath('kozeta_currency/coin/edit', ['coin_id' => $coin->getId()]);
            } else {
                $resultRedirect->setPath('kozeta_currency/coin');
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            if ($coin != null) {
                $this->storeCoinDataToSession(
                    $this->dataObjectProcessor->buildOutputDataArray(
                        $coin,
                        CoinInterface::class
                    )
                );
            }
            $resultRedirect->setPath('kozeta_currency/coin/edit', ['coin_id' => $id]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('There was a problem saving the coin'.$e->getMessage()));
            if ($coin != null) {
                $this->storeCoinDataToSession(
                    $this->dataObjectProcessor->buildOutputDataArray(
                        $coin,
                        CoinInterface::class
                    )
                );
            }
            $resultRedirect->setPath('kozeta_currency/coin/edit', ['coin_id' => $id]);
        }
        
        $symbolsDataArray = [$data['code'] => $data['symbol']];
        try {
            $this->_objectManager->create(\Magento\CurrencySymbol\Model\System\Currencysymbol::class)
                ->setCurrencySymbolsData($symbolsDataArray);
            //$this->messageManager->addSuccess(__('Congratulations!'));
        } catch (\Exception $e) {
            //$this->log->logError($e);
            $this->messageManager->addError($e->getMessage());
        }
        
        
        
        return $resultRedirect;
    }

    /**
     * @param $type
     * @return Uploader
     * @throws \Exception
     */
    protected function getUploader($type)
    {
        return $this->uploaderPool->getUploader($type);
    }

    /**
     * @param $coinData
     */
    protected function storeCoinDataToSession($coinData)
    {
        $this->_getSession()->setKozetaCurrencyCoinData($coinData);
    }
}
