<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /** @since 1.0.2 Per currency import service and schedule assigned */
        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->addColumnImportScheduler($installer);
            $this->addIndexImportScheduler($installer);
            $this->addColumnConverterId($installer);
        }

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     * @return void
     */
    protected function addColumnImportScheduler(SchemaSetupInterface $installer)
    {
        $installer->getConnection()->addColumn(
            $installer->getTable('kozeta_currency_coin'),
            'import_scheduler',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'default' => '',
                'comment' => 'Import Scheduler'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $installer
     * @return void
     */
    protected function addColumnImportEnabled(SchemaSetupInterface $installer)
    {
        $installer->getConnection()->addColumn(
            $installer->getTable('kozeta_currency_coin'),
            'import_enabled',
            [
                'type' => Table::TYPE_SMALLINT,
                'length' => 1,
                'nullable' => false,
                'default' => '1',
                'comment' => 'Import Enabled'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $installer
     * @return void
     */
    private function addIndexImportScheduler(SchemaSetupInterface $installer)
    {
        $installer->getConnection()->addIndex(
            $installer->getTable('kozeta_currency_coin'),
            $installer->getIdxName('kozeta_currency_coin', ['import_scheduler']),
            ['import_scheduler']
        );
    }
    
    /**
     * @param SchemaSetupInterface $installer
     * @return void
     */
    private function addColumnConverterId(SchemaSetupInterface $installer)
    {
        $installer->getConnection()->addColumn(
            $installer->getTable('kozeta_currency_currency_rate'),
            'currency_converter_id',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'default' => '',
                'comment' => 'Currency Converter ID'
            ]
        );
    }
}
