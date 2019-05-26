<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Setup;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Config\Model\ResourceModel\Config\Data;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;

class Uninstall implements UninstallInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var Data
     */
    private $configResource;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Data $configResource
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Data $configResource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->configResource    = $configResource;
    }

    /**
     * Drop tables of the module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        //remove tables
        if ($setup->tableExists('kozeta_currency_coin_store')) {
            $setup->getConnection()->dropTable('kozeta_currency_coin_store');
        }
        if ($setup->tableExists('kozeta_currency_coin')) {
            $setup->getConnection()->dropTable('kozeta_currency_coin');
        }
        if ($setup->tableExists('kozeta_currency_currency_rate')) {
            $setup->getConnection()->dropTable('kozeta_currency_currency_rate');
        }
        //remove config settings if any
        $collection = $this->collectionFactory->create()
            ->addPathFilter('kozeta_currency');
        foreach ($collection as $config) {
            $this->deleteConfig($config);
        }
    }

    /**
     * @param AbstractModel $config
     * @throws \Exception
     */
    protected function deleteConfig(AbstractModel $config)
    {
        $this->configResource->delete($config);
    }
}
