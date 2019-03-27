<?php
/**
 * @author Kozeta Team
 * @copyright Copyright (c) 2019 Kozeta (https://www.kozeta.lt)
 * @package Kozeta_Curency
 */

namespace Kozeta\Currency\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->addNewTables($setup);
        $this->changeCurrencyColumnLength($setup);
    }



    private function addNewTables($setup)
    {

        $installer = $setup;

        $installer->startSetup();
        
        if (!$installer->tableExists('kozeta_currency_currency_rate')) {



            /**
             * Create table 'directory_currency_rate'
             */
            $table = $installer->getConnection()->newTable(
                $installer->getTable('kozeta_currency_currency_rate')
            )->addColumn(
                'currency_from',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['nullable' => false, 'primary' => true, 'default' => false],
                'Currency Code Convert From'
            )->addColumn(
                'currency_to',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['nullable' => false, 'primary' => true, 'default' => false],
                'Currency Code Convert To'
            )->addColumn(
                'rate',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '36,24',
                ['nullable' => false, 'default' => '0.000000000000'],
                'Currency Conversion Rate'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Last Updated'
            )->addIndex(
                $installer->getIdxName('kozeta_currency_currency_rate', ['currency_to']),
                ['currency_to']
            )->setComment(
                'Kozeta Currency Rate'
            );
            $installer->getConnection()->createTable($table);
        }


        if (!$installer->tableExists('kozeta_currency_coin')) {
            $table = $installer->getConnection()->newTable($installer->getTable('kozeta_currency_coin'));
            
            $table->addColumn(
                'coin_id',
                Table::TYPE_INTEGER,
                null,
                [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                'Coin ID'
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['nullable'  => false,],
                'Coin Name'
            )->addColumn(
                'url_key',
                Table::TYPE_TEXT,
                255,
                ['nullable'  => false,],
                'Coin Url Key'
            )->addColumn(
                'description',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Coin Description'
            )->addColumn(
                'code',
                Table::TYPE_TEXT,
                64,
                ['nullable'  => false,],
                'Currency code'
            )->addColumn(
                'is_fiat',
                Table::TYPE_BOOLEAN,
                1,
                ['nullable' => false, 'default' => '0'],
                'Fiat or crypto'
            )->addColumn(
                'type',
                Table::TYPE_INTEGER,
                null,
                [],
                'Coin Type'
            )->addColumn(
                'avatar',
                Table::TYPE_TEXT,
                255,
                [],
                'Coin Avatar'
            )->addColumn(
                'symbol',
                Table::TYPE_TEXT,
                16,
                [],
                'Coin Symbol'
            )->addColumn(
                'meta_title',
                Table::TYPE_TEXT,
                255,
                [],
                'Coin Meta Title'
            )->addColumn(
                'meta_description',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Coin Meta Description'
            )->addColumn(
                'meta_keywords',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Coin Meta Keywords'
            )->addColumn(
                'is_active',
                Table::TYPE_INTEGER,
                null,
                [
                        'nullable'  => false,
                        'default'   => '1',
                    ],
                'Is Coin Active'
            )->addColumn(
                'in_rss',
                Table::TYPE_INTEGER,
                null,
                [
                        'nullable'  => false,
                        'default'   => '1',
                    ],
                'Show in rss'
            )->addColumn(
                'sort_order',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Coin Sort Order'
            )->addColumn(
                'txfee',
                Table::TYPE_DECIMAL,
                [12,8],
                ['nullable' => false, 'default' => '0.00000000'],
                'Recommended transaction fee'
            )->addColumn(
                'minconf',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '2','unsigned' => true],
                'Minimum number of confirmations'
            )->addColumn(
                'currency_converter_id',
                Table::TYPE_TEXT,
                255,
                [],
                'Currency Convert Service'
            )->addColumn(
                'precision',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '2','unsigned' => true],
                'Number of decimals'
            )->addIndex(
                $installer->getIdxName('kozeta_currency_coin', ['is_active']),
                ['is_active']
            )->addIndex(
                $installer->getIdxName('kozeta_currency_coin', ['currency_converter_id']),
                ['currency_converter_id']
            )->addIndex(
                $installer->getIdxName('kozeta_currency_coin', ['code', 'coin_id'], AdapterInterface::INDEX_TYPE_UNIQUE),
                ['code', 'coin_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )->setComment('Currency coins');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('kozeta_currency_coin'),
                $setup->getIdxName(
                    $installer->getTable('kozeta_currency_coin'),
                    ['name','photo'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                [
                    'name',
                    'description',
                    'url_key',
                    'meta_title',
                    'meta_keywords',
                    'meta_description'
                ],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }

        //Create Coins to Store table
        if (!$installer->tableExists('kozeta_currency_coin_store')) {
            $table = $installer->getConnection()->newTable($installer->getTable('kozeta_currency_coin_store'));
            $table->addColumn(
                'coin_id',
                Table::TYPE_INTEGER,
                null,
                [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'   => true,
                    ],
                'Coin ID'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                [
                        'unsigned'  => true,
                        'nullable'  => false,
                        'primary'   => true,
                    ],
                'Store ID'
            )->addIndex(
                $installer->getIdxName('kozeta_currency_coin_store', ['store_id']),
                ['store_id']
            )->addForeignKey(
                $installer->getFkName('kozeta_currency_coin_store', 'coin_id', 'kozeta_currency_coin', 'coin_id'),
                'coin_id',
                $installer->getTable('kozeta_currency_coin'),
                'coin_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName('kozeta_currency_coin_store', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->setComment('Coin To Store Link Table');
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function changeCurrencyColumnLength(SchemaSetupInterface $setup)
    {

        $installer = $setup;

        $installer->startSetup();
        
        $tableName = $setup->getTable('paypal_settlement_report_row');

        $setup->getConnection()->changeColumn(
            $tableName,
            'gross_transaction_currency',
            'gross_transaction_currency',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Gross Transaction Currency'
            ]
        );

        $setup->getConnection()->changeColumn(
            $tableName,
            'fee_currency',
            'fee_currency',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Fee Currency'
            ]
        );

/** TABLE quote **/
        $tableName = $setup->getTable('quote');

        $setup->getConnection()->changeColumn(
            $tableName,
            'base_currency_code',
            'base_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Base Currency Code'
            ]
        );

        $setup->getConnection()->changeColumn(
            $tableName,
            'store_currency_code',
            'store_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Store Currency Code'
            ]
        );
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'quote_currency_code',
            'quote_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Quote Currency Code'
            ]
        );
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'global_currency_code',
            'global_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Global Currency Code'
            ]
        );

        $setup->getConnection()->modifyColumn(
            $tableName,
            'grand_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Grand Total'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'subtotal',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',

                'default' => '0',
                'comment' => 'Subtotal'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'subtotal_with_discount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Subtotal With Discount'
            ]
        );
// for base currency
        $setup->getConnection()->modifyColumn(
            $tableName,
            'store_to_base_rate',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Store To Base Rate',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'store_to_quote_rate',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Store To Quote Rate',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_grand_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Base Grand Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_to_global_rate',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base To Global Rate',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_to_quote_rate',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base To Quote Rate',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_subtotal',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Subtotal',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_subtotal_with_discount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Subtotal With Discount',
            ]
        );

/** TABLE quote_address **/
        $tableName = $setup->getTable('quote_address');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'subtotal',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Subtotal'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'subtotal_with_discount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Subtotal With Discount'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Tax Amount'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Shipping Amount'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Shipping Tax Amount'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Discount Amount'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'grand_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Grand Total'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Discount Amount'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'subtotal_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Subtotal Including Tax'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Tax Compensation Amount'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Discount Tax Compensation Amount'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Including Tax'
            ]
        );
// for base currency (quote_address)
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_subtotal',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Base Subtotal',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_subtotal_with_discount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Base Subtotal With Discount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Base Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Base Shipping Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Base Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_grand_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Base Grand Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_subtotal_total_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Subtotal Total Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Tax Compensation Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_discount_tax_compensation_amnt',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Discount Tax Compensation Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Incl Tax',
            ]
        );

        
/** TABLE quote_address_item **/
        $tableName = $setup->getTable('quote_address_item');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Discount Amount'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'row_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Row total'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_percent',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Tax Percent'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'price_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Price Including Tax'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'row_total_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Row Total Including Tax'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Discount Tax Compensation Amount'
            ]
        );
// For base currency
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_row_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Base Row Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'row_total_with_discount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Row Total With Discount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Base Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Base Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'price',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Price',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_percent',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Percent',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_price',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Price',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_cost',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Cost',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_price_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Price Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_row_total_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Row Total Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Base Discount Tax Compensation Amount',
            ]
        );

        
/** TABLE quote_item **/
        $tableName = $setup->getTable('quote_item');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Amount'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Tax Amount'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'row_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Row total'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'row_total_with_discount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Row total with discount'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'price_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Price Including Tax'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'row_total_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Row Total Including Tax'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Tax Compensation Amount'
            ]
        );
// for base currency
        $setup->getConnection()->modifyColumn(
            $tableName,
            'price',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Price',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_price',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Base Price',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'custom_price',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Custom Price',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_percent',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Discount Percent',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Base Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_percent',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Tax Percent',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Base Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_row_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Base Row Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_tax_before_discount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Tax Before Discount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_before_discount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Tax Before Discount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'original_custom_price',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Original Custom Price',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_cost',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Cost',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_price_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Price Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_row_total_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Row Total Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Tax Compensation Amount',
            ]
        );

/* Table quote_shipping_rate */
        $tableName = $setup->getTable('quote_shipping_rate');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'price',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Price',
            ]
        );


/* Table sales_creditmemo */
        $tableName = $setup->getTable('sales_creditmemo');
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'base_currency_code',
            'base_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Base Currency Code'
            ]
        );
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'store_currency_code',
            'store_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Store Currency Code'
            ]
        );
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'order_currency_code',
            'order_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Order Currency Code'
            ]
        );
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'global_currency_code',
            'global_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Global Currency Code'
            ]
        );

        $setup->getConnection()->modifyColumn(
            $tableName,
            'adjustment_positive',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Adjustment Positive'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Tax Amount'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'store_to_order_rate',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Store To Order Rate',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_to_order_rate',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base To Order Rate',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'grand_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Grand Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_adjustment_negative',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Adjustment Negative',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_subtotal_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Subtotal Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'subtotal_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Subtotal Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'adjustment_negative',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Adjustment Negative',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'store_to_base_rate',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Store To Base Rate',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_to_global_rate',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base To Global Rate',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_adjustment',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Adjustment',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_adjustment',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Adjustment',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_subtotal',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Subtotal',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'subtotal',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Subtotal',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'adjustment',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Adjustment',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_grand_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Grand Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_adjustment_positive',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Adjustment Positive',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Tax Compensation Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Tax Compensation Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Discount Tax Compensation Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_discount_tax_compensation_amnt',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Discount Tax Compensation Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Incl Tax',
            ]
        );

/* Table sales_invoice */
        $tableName = $setup->getTable('sales_invoice');
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'base_currency_code',
            'base_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Base Currency Code'
            ]
        );
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'store_currency_code',
            'store_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Store Currency Code'
            ]
        );
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'order_currency_code',
            'order_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Order Currency Code'
            ]
        );
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'global_currency_code',
            'global_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Global Currency Code'
            ]
        );

        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_grand_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Grand Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'store_to_order_rate',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Store To Order Rate',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_to_order_rate',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base To Order Rate',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'grand_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Grand Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'subtotal_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Subtotal Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_subtotal_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Subtotal Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'store_to_base_rate',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Store To Base Rate',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_to_global_rate',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base To Global Rate',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'subtotal',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Subtotal',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_subtotal',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Subtotal',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Tax Compensation Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Tax Compensation Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Discount Tax Compensation Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_discount_tax_compensation_amnt',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Discount Tax Compensation Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_total_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Total Refunded',
            ]
        );

/* Table sales_invoice_grid */
        $tableName = $setup->getTable('sales_invoice_grid');
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'base_currency_code',
            'base_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Base Currency Code'
            ]
        );
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'store_currency_code',
            'store_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Store Currency Code'
            ]
        );
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'order_currency_code',
            'order_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Order Currency Code'
            ]
        );
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'global_currency_code',
            'global_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Global Currency Code'
            ]
        );

        $setup->getConnection()->modifyColumn(
            $tableName,
            'subtotal',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Subtotal',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_and_handling',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping and handling amount',
            ]
        );
// ref https://github.com/magento/magento2/issues/5546
// check if column exists. Add it otherwise.
        $_fields = $setup->getConnection()->describeTable('sales_invoice_grid');
        $fields = array_keys($_fields);
        if (in_array('base_grand_total', $fields)) {
            $setup->getConnection()->modifyColumn(
                $tableName,
                'base_grand_total',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '18,10',
                    'comment' => 'Base Grand Total',
                ]
            );
        } else {
            $setup->getConnection()->addColumn(
                $tableName,
                'base_grand_total',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'comment' => 'Base Grand Total',
                    'after' => 'grand_total'
                ]
            );
        }
        $setup->getConnection()->modifyColumn(
            $tableName,
            'grand_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Grand Total',
            ]
        );

/* Table sales_order */
        
        $tableName = $setup->getTable('sales_order');
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'base_currency_code',
            'base_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Base Currency Code'
            ]
        );
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'store_currency_code',
            'store_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Store Currency Code'
            ]
        );
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'order_currency_code',
            'order_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Order Currency Code'
            ]
        );
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'global_currency_code',
            'global_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Global Currency Code'
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_canceled',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Canceled',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_grand_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Grand Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_canceled',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Canceled',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_tax_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Tax Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_subtotal',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Subtotal',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_subtotal_canceled',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Subtotal Canceled',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_subtotal_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Subtotal Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_subtotal_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Subtotal Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_tax_canceled',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Tax Canceled',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_tax_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Tax Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_tax_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Tax Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_to_global_rate',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base To Global Rate',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_to_order_rate',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base To Order Rate',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_total_canceled',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Total Canceled',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_total_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Total Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_total_invoiced_cost',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Total Invoiced Cost',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_total_offline_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Total Offline Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_total_online_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Total Online Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_total_paid',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Total Paid',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_total_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Total Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_canceled',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Canceled',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'grand_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Grand Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_canceled',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Canceled',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_tax_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Tax Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'store_to_base_rate',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Store To Base Rate',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'store_to_order_rate',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Store To Order Rate',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'subtotal',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Subtotal',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'subtotal_canceled',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Subtotal Canceled',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'subtotal_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Subtotal Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'subtotal_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Subtotal Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_canceled',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Tax Canceled',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Tax Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Tax Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_canceled',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Total Canceled',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Total Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_offline_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Total Offline Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_online_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Total Online Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_paid',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Total Paid',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Total Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'adjustment_negative',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Adjustment Negative',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'adjustment_positive',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Adjustment Positive',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_adjustment_negative',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Adjustment Negative',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_adjustment_positive',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Adjustment Positive',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_subtotal_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Subtotal Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_total_due',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Total Due',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'payment_authorization_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Payment Authorization Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'subtotal_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Subtotal Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_due',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Total Due',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Tax Compensation Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Tax Compensation Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Discount Tax Compensation Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_discount_tax_compensation_amnt',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Discount Tax Compensation Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_tax_compensation_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Tax Compensation Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_tax_compensation_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Tax Compensation Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_tax_compensation_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Tax Compensation Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_tax_compensation_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Tax Compensation Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Incl Tax',
            ]
        );


/* Table sales_order_grid */
        
        $tableName = $setup->getTable('sales_order_grid');
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'base_currency_code',
            'base_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Base Currency Code'
            ]
        );
        
        $setup->getConnection()->changeColumn(
            $tableName,
            'order_currency_code',
            'order_currency_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'Order Currency Code'
            ]
        );

        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_grand_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Grand Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_total_paid',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Total Paid',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'grand_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Grand Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_paid',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Total Paid',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'subtotal',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Subtotal',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_and_handling',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping and handling amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Total Refunded',
            ]
        );
 /* Table `sales_order_tax`; */

        $tableName = $setup->getTable('sales_order_tax');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'percent',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Percent',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_real_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Real Amount',
            ]
        );
        
 /* Table `sales_creditmemo_grid`; */
        $tableName = $setup->getTable('sales_creditmemo_grid');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_grand_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Grand Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'subtotal',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Subtotal',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_and_handling',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping and handling amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'adjustment_positive',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Adjustment Positive',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'adjustment_negative',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Adjustment Negative',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'order_base_grand_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Order Grand Total',
            ]
        );
        
 /* Table `sales_bestsellers_aggregated_daily`; */
        $tableName = $setup->getTable('sales_bestsellers_aggregated_daily');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'product_price',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Product Price',
            ]
        );
        
 /* Table `sales_bestsellers_aggregated_monthly`; */
        $tableName = $setup->getTable('sales_bestsellers_aggregated_monthly');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'product_price',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Product Price',
            ]
        );
        
 /* Table `sales_bestsellers_aggregated_yearly` */
        $tableName = $setup->getTable('sales_bestsellers_aggregated_yearly');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'product_price',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Product Price',
            ]
        );
        
  /* Table `sales_creditmemo_item` */
        $tableName = $setup->getTable('sales_creditmemo_item');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_price',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Price',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_row_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Row Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'row_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Row Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'price_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Price Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_price_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Price Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_cost',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Cost',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'price',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Price',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_row_total_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Row Total Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'row_total_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Row Total Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Tax Compensation Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Tax Compensation Amount',
            ]
        );
        
 /* Table `sales_invoice_item` */
        $tableName = $setup->getTable('sales_invoice_item');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_price',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Price',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_row_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Row Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'row_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Row Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'price_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Price Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_price_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Price Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_cost',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Cost',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'price',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Price',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_row_total_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Row Total Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'row_total_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Row Total Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Tax Compensation Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Tax Compensation Amount',
            ]
        );

 /* Table `sales_invoiced_aggregated` */
        $tableName = $setup->getTable('sales_invoiced_aggregated');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'orders_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Orders Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'invoiced_captured',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Invoiced Captured',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'invoiced_not_captured',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Invoiced Not Captured',
            ]
        );

 /* Table `sales_invoiced_aggregated_order` */
        $tableName = $setup->getTable('sales_invoiced_aggregated_order');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'orders_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Orders Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'invoiced_captured',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Invoiced Captured',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'invoiced_not_captured',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Invoiced Not Captured',
            ]
        );

 /* Table `sales_order_aggregated_created` */
        $tableName = $setup->getTable('sales_order_aggregated_created');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_income_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Income Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_revenue_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Revenue Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_profit_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Profit Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_invoiced_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Invoiced Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_canceled_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Canceled Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_paid_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Paid Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_refunded_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Refunded Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_tax_amount_actual',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Tax Amount Actual',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_shipping_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Shipping Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_shipping_amount_actual',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Shipping Amount Actual',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_discount_amount_actual',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Discount Amount Actual',
            ]
        );

 /* Table `sales_order_aggregated_updated` */
        $tableName = $setup->getTable('sales_order_aggregated_updated');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_income_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Income Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_revenue_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Revenue Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_profit_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Profit Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_invoiced_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Invoiced Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_canceled_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Canceled Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_paid_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Paid Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_refunded_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Refunded Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_tax_amount_actual',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Tax Amount Actual',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_shipping_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Shipping Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_shipping_amount_actual',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Shipping Amount Actual',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_discount_amount_actual',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Total Discount Amount Actual',
            ]
        );

 /* Table `sales_order_item` */
        $tableName = $setup->getTable('sales_order_item');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_cost',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' =>  'Base Cost',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'price',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' =>  'Price',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_price',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Base Price',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'original_price',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Original Price',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_original_price',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Original Price',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_percent',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Tax Percent',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Base Tax Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Tax Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_tax_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Base Tax Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_percent',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Discount Percent',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Base Discount Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Discount Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Base Discount Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'amount_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Amount Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_amount_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'default' => '0',
                'comment' => 'Base Amount Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'row_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Row Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_row_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Base Row Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'row_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Row Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_row_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'nullable' => false,
                'default' => '0',
                'comment' => 'Base Row Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_tax_before_discount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Tax Before Discount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_before_discount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Tax Before Discount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'price_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Price Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_price_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Price Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'row_total_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Row Total Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_row_total_incl_tax',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Row Total Incl Tax',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Tax Compensation Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_tax_compensation_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Tax Compensation Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_tax_compensation_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Tax Compensation Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_tax_compensation_invoiced',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Tax Compensation Invoiced',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_tax_compensation_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Tax Compensation Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_tax_compensation_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Tax Compensation Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_canceled',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Tax Canceled',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_tax_compensation_canceled',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Tax Compensation Canceled',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Tax Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_tax_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Tax Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'discount_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Discount Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_discount_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Discount Refunded',
            ]
        );

 /* Table `sales_order_payment` */
        $tableName = $setup->getTable('sales_order_payment');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_captured',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Captured',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_captured',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Captured',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'amount_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Amount Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_amount_paid',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Amount Paid',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'amount_canceled',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Amount Canceled',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_amount_authorized',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Amount Authorized',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_amount_paid_online',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Amount Paid Online',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_amount_refunded_online',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Amount Refunded Online',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Amount',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'amount_paid',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Amount Paid',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'amount_authorized',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Amount Authorized',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_amount_ordered',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Amount Ordered',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_shipping_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Shipping Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'shipping_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Shipping Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_amount_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Amount Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'amount_ordered',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Amount Ordered',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_amount_canceled',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base Amount Canceled',
            ]
        );

 /* Table `sales_order_tax_item` */
        $tableName = $setup->getTable('sales_order_tax_item');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'tax_percent',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Real Tax Percent For Item',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Tax amount for the item and tax rate',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'base_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Base tax amount for the item and tax rate',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'real_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Real tax amount for the item and tax rate',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'real_base_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Real base tax amount for the item and tax rate',
            ]
        );

 /* Table `sales_refunded_aggregated` */
        $tableName = $setup->getTable('sales_refunded_aggregated');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'online_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Online Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'offline_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Offline Refunded',
            ]
        );

 /* Table `sales_refunded_aggregated_order` */
        $tableName = $setup->getTable('sales_refunded_aggregated_order');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'online_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Online Refunded',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'offline_refunded',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Offline Refunded',
            ]
        );

 /* Table `sales_shipment_item` */
        $tableName = $setup->getTable('sales_shipment_item');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'row_total',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Row Total',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'price',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Price',
            ]
        );

 /* Table `sales_shipping_aggregated` */
        $tableName = $setup->getTable('sales_shipping_aggregated');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_shipping',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Total Shipping',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_shipping_actual',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Total Shipping Actual',
            ]
        );
        
 /* Table `sales_shipping_aggregated_order` */
        $tableName = $setup->getTable('sales_shipping_aggregated_order');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_shipping',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Total Shipping',
            ]
        );
        $setup->getConnection()->modifyColumn(
            $tableName,
            'total_shipping_actual',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '18,10',
                'comment' => 'Total Shipping Actual',
            ]
        );

        $installer->endSetup();
    }
}
