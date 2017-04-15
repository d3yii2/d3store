<?php

use yii\db\Migration;

class m170414_061906_init extends Migration
{
    public function up()
    {
        $sql = "
            SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
        ";
        $this->execute($sql);

        $sql = "
            CREATE TABLE `store_ref` (
              `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(50) DEFAULT NULL COMMENT 'Name',
              `class_name` varchar(255) DEFAULT NULL COMMENT 'Class Name',
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;        
        ";
        $this->execute($sql);

        $sql = "
            CREATE TABLE `store_stack` (
              `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
              `store_id` smallint(5) unsigned NOT NULL COMMENT 'Store',
              `name` varchar(50) DEFAULT NULL COMMENT 'Stack name',
              `type` enum('Standard','Tehnical') DEFAULT NULL COMMENT 'Type',
              `product_name` varchar(255) DEFAULT NULL COMMENT 'Product',
              `capacity` int(11) DEFAULT NULL COMMENT 'Capacity',
              `notes` text COMMENT 'Notes',
              `active` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT 'Active',
              PRIMARY KEY (`id`),
              KEY `store_id` (`store_id`),
              CONSTRAINT `store_stack_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `store_store` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;        
        ";
        $this->execute($sql);

        $sql = "
            CREATE TABLE `store_store` (
              `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
              `company_id` smallint(5) unsigned NOT NULL,
              `name` varchar(50) DEFAULT NULL COMMENT 'Store Name',
              `address` varchar(255) DEFAULT NULL COMMENT 'Store Address',
              `capacity` tinyint(4) DEFAULT NULL COMMENT 'Capacity',
              `active` tinyint(4) DEFAULT '1' COMMENT 'Active',
              PRIMARY KEY (`id`),
              KEY `sys_company_id` (`company_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;        
        ";
        $this->execute($sql);

        $sql = "
            CREATE TABLE `store_transactions` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `action` enum('Load','Unload','Move') NOT NULL COMMENT 'Action',
              `tran_time` datetime NOT NULL COMMENT 'Time',
              `stack_from` smallint(5) unsigned DEFAULT NULL COMMENT 'From stack',
              `stack_to` smallint(5) unsigned DEFAULT NULL COMMENT 'To stack',
              `quantity` decimal(13,3) NOT NULL COMMENT 'quantity',
              `remain_quantity` decimal(13,0) NOT NULL COMMENT 'Remain quantity',
              `ref_id` tinyint(3) unsigned DEFAULT NULL COMMENT 'Refernce model',
              `ref_record_id` int(10) unsigned DEFAULT NULL COMMENT 'Reference model record',
              PRIMARY KEY (`id`),
              KEY `stack_from` (`stack_from`),
              KEY `stack_to` (`stack_to`),
              KEY `ref_id` (`ref_id`),
              CONSTRAINT `store_transactions_ibfk_1` FOREIGN KEY (`stack_from`) REFERENCES `store_stack` (`id`),
              CONSTRAINT `store_transactions_ibfk_2` FOREIGN KEY (`stack_to`) REFERENCES `store_stack` (`id`),
              CONSTRAINT `store_transactions_ibfk_3` FOREIGN KEY (`ref_id`) REFERENCES `store_ref` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;        
        ";
        $this->execute($sql);

        $sql = "
            CREATE TABLE `store_woff` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `load_tran_id` int(10) unsigned NOT NULL COMMENT 'Load transaction',
              `unload_tran_id` int(10) unsigned DEFAULT NULL COMMENT 'Unload transaction',
              `quantity` decimal(13,0) DEFAULT '3' COMMENT 'Quantity',
              PRIMARY KEY (`id`),
              KEY `load_tran_id` (`load_tran_id`),
              KEY `unload_tran_id` (`unload_tran_id`),
              CONSTRAINT `store_woff_ibfk_1` FOREIGN KEY (`load_tran_id`) REFERENCES `store_transactions` (`id`),
              CONSTRAINT `store_woff_ibfk_2` FOREIGN KEY (`unload_tran_id`) REFERENCES `store_transactions` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;        
        ";

        $this->execute($sql);
        $sql = "
            SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
        ";
        $this->execute($sql);

    }

    public function down()
    {
        echo "m170415_061906_init cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
