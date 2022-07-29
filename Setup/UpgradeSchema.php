<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://venustheme.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_All
 * @copyright  Copyright (c) 2017 Landofcoder (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

namespace Ves\All\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $setup->getConnection()->dropTable($setup->getTable('ves_all_license'));
            $table = $installer->getConnection()->newTable(
                $installer->getTable('ves_all_license')
                )
            ->addColumn(
                'license_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'License ID'
                )
            ->addColumn(
                'extension_code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Extension Code'
                )
            ->addColumn(
                'extension_name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Extension Name'
                )
            ->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Status'
                )
            ->addIndex(
                $setup->getIdxName('ves_all_license', ['license_id']),
                ['license_id']
                );
            $installer->getConnection()->createTable($table);
        }
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('ves_all_license'),
                'license',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'License key'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('ves_all_license'),
                'description',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'module description'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable('ves_all_license'),
                'created_at',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => true,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT,
                    'comment' => 'created at'
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable('ves_all_license'),
                'expired_time',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => true,
                    'comment' => 'expired time'
                ]
            );
        }
        $installer->endSetup();
    }
}