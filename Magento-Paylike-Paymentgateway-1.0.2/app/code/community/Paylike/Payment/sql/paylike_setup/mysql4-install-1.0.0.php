<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$connection = $installer->getConnection();
$paylikeAdminTable = $installer->getTable('paylike_payment/paylikeadmin');
$paylikeLogosTable = $installer->getTable('paylike_payment/paylikelogos');
$connection->addColumn($installer->getTable('sales/quote_payment'),
    'paylike_transaction_id',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 255,
        'nullable' => false,
        'default' => 0,
        'comment' => 'Paylike transaction id'
    )
);

$connection->addColumn($installer->getTable('sales/order_payment'),
    'paylike_transaction_id',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 255,
        'nullable' => false,
        'default' => 0,
        'comment' => 'Paylike transaction id'
    )
);

if (!$connection->isTableExists($paylikeAdminTable)) {
    $table = $connection->newTable($paylikeAdminTable)
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
            'identity' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Row Id')
        ->addColumn('paylike_tid', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false
        ), 'Transaction id')
        ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
            'nullable' => false,
        ), 'Order Id')
        ->addColumn('payed_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable' => false,
        ), 'Time payed')
        ->addColumn('payed_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '20,6', array(
            'nullable' => false,
        ), 'Amount payed')
        ->addColumn('refunded_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '20,6', array(
            'nullable' => false,
        ), 'Amount refunded')
        ->addColumn('captured', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false
        ), 'Captured flag')
        ->setComment('Paylike transaction table');
    $connection->createTable($table);
}
if (!$connection->isTableExists($paylikeLogosTable)) {
    $table = $connection->newTable($paylikeLogosTable)
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
            'identity' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Row Id')
        ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false
        ), 'Logo name')
        ->addColumn('slug', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
        ), 'Logo slug')
        ->addColumn('file_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable' => false,
        ), 'File name')
        ->addColumn('default_logo', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
            'nullable' => false,
            'default' => 1
        ), 'Default logo flag')
        ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable' => false,
        ), 'Creation time of the logo')
        ->setComment('Paylike logos table');
    $installer->getConnection()->createTable($table);
}

// insert logos
$value_1 = array(
    'id' => 1,
    'name' => 'VISA',
    'slug' => 'visa',
    'file_name' => 'visa.svg',
    'default_logo' => '1'
);
$table = $installer->getConnection()->insertOnDuplicate(
    $this->getTable('paylike_payment/paylikelogos'),
    $value_1,
    array('id')
);

$value_2 = array(
    'id' => 2,
    'name' => 'VISA Electron',
    'slug' => 'visa-electron',
    'file_name' => 'visa-electron.svg',
    'default_logo' => '1'
);

$table = $installer->getConnection()->insertOnDuplicate(
    $this->getTable('paylike_payment/paylikelogos'),
    $value_2,
    array('id')
);

$value_3 = array(
    'id' => 3,
    'name' => 'Mastercard',
    'slug' => 'mastercard',
    'file_name' => 'mastercard.svg',
    'default_logo' => '1'
);

$table = $installer->getConnection()->insertOnDuplicate(
    $this->getTable('paylike_payment/paylikelogos'),
    $value_3,
    array('id')
);

$value_4 = array(
    'id' => 4,
    'name' => 'Mastercard Maestro',
    'slug' => 'mastercard-maestro',
    'file_name' => 'mastercard-maestro.svg',
    'default_logo' => '1'
);

$table = $installer->getConnection()->insertOnDuplicate(
    $this->getTable('paylike_payment/paylikelogos'),
    $value_4,
    array('id')
);

$installer->endSetup();
