<?php

use yii\db\Migration;

class m200824_170707_fix_add  extends Migration {

    public function safeUp() {

        $this->execute('
            CREATE TABLE `store_fixes` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `transaction_id` int(10) unsigned NOT NULL,
              `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              `user_id` int(10) unsigned DEFAULT NULL,
              `quantity` decimal(13,3) DEFAULT NULL,
              `ref_model_id` tinyint(3) unsigned DEFAULT NULL,
              `ref_model_record_id` int(10) unsigned DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `store_fixes_ibfk_transaction` (`transaction_id`),
              KEY `store_fixes_ibfk_model` (`ref_model_id`),
              CONSTRAINT `store_fixes_ibfk_model` FOREIGN KEY (`ref_model_id`) REFERENCES `sys_models` (`id`),
              CONSTRAINT `store_fixes_ibfk_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `store_transactions` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1

        ');

    }

    public function safeDown() {
        echo "m200824_170707_fix_add cannot be reverted.\n";
        return false;
    }
}
