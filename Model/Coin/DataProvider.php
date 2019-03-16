<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Model\Coin;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Kozeta\Currency\Model\ResourceModel\Coin\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var PoolInterface
     */
    protected $pool;
    
    public $request;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $coinCollectionFactory
     * @param PoolInterface $pool
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $coinCollectionFactory,
        PoolInterface $pool,
        //\Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Request\Http  $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collection   = $coinCollectionFactory->create();
        $this->pool         = $pool;
        
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
        $this->request		= $request;
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }
        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->data = $modifier->modifyData($this->data);
        	$coin_id = $this->request->getParam('coin_id');
			$data = $this->data;
			
        	foreach ($data as $id => $coin) {
        		if (!isset($coin['is_fiat'])) {
        			continue;
        		}
        		if ($id != $coin_id) {
        			unset($data[$id]);
        			continue;
        		}
        		if($coin['is_fiat']) {
        			$data[$id]['checked'] = true;
        		} else {
        			$data[$id]['checked'] = false;
        		}
        	}
        	$this->data = $data;
        
        }

        return $this->data;
    }
}
