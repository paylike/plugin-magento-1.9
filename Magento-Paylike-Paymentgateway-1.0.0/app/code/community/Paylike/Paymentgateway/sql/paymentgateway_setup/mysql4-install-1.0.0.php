<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE `{$installer->getTable('sales/quote_payment')}`
        ADD `paylike_transaction_id` VARCHAR( 255 ) NOT NULL;

    ALTER TABLE `{$installer->getTable('sales/order_payment')}`
        ADD `paylike_transaction_id` VARCHAR( 255 ) NOT NULL;

    DROP TABLE IF EXISTS `{$this->getTable('paymentgateway/paylikeadmin')}`;
    CREATE TABLE `{$this->getTable('paymentgateway/paylikeadmin')}` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `paylike_tid` varchar(255) NOT NULL,
        `order_id` int(11) NOT NULL,
        `payed_at` datetime NOT NULL,
        `payed_amount` decimal(20,6) NOT NULL,
        `refunded_amount` decimal(20,6) NOT NULL,
        `captured` varchar(255) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

    DROP TABLE IF EXISTS `{$this->getTable('paymentgateway/paylikelogos')}`;
    CREATE TABLE `{$this->getTable('paymentgateway/paylikelogos')}` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `slug` varchar(255) NOT NULL,
        `file_name` varchar(255) NOT NULL,
        `default_logo` int(11) NOT NULL DEFAULT '1' COMMENT '1=Default',
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

    INSERT INTO `{$this->getTable('paymentgateway/paylikelogos')}` (`id`, `name`, `slug`, `file_name`, `default_logo`) VALUES
    (1, 'VISA', 'visa', 'visa.svg', 1),
    (2, 'VISA Electron', 'visa-electron', 'visa-electron.svg', 1),
    (3, 'Mastercard', 'mastercard', 'mastercard.svg', 1),
    (4, 'Mastercard Maestro', 'mastercard-maestro', 'mastercard-maestro.svg', 1);
");
$installer->endSetup();
