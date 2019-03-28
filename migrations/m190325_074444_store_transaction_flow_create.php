<?php

use yii\db\Migration;

/**
* Class m190325_074444_store_transaction_flow_create*/
class m190325_074444_store_transaction_flow_create extends Migration
{
    /**
    * {@inheritdoc}
    */
    public function safeUp()
    {
        $this->execute('
            CREATE TABLE `store_transaction_flow` (
              `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
              `prev_tran_id` INT(10) UNSIGNED NOT NULL COMMENT \'Prev transaction\',
              `next_tran_id` INT(10) UNSIGNED DEFAULT NULL COMMENT \'Next transaction\',
              `quantity` DECIMAL(13,3) DEFAULT \'3.000\' COMMENT \'Quantity\',
              PRIMARY KEY (`id`),
              KEY `prev_tran_id` (`prev_tran_id`),
              KEY `next_tran_id` (`next_tran_id`),
              CONSTRAINT `store_tran_flow_ibfk_prev` FOREIGN KEY (`prev_tran_id`) REFERENCES `store_transactions` (`id`),
              CONSTRAINT `store_tran_flow_ibfk_next` FOREIGN KEY (`next_tran_id`) REFERENCES `store_transactions` (`id`)
            ) ENGINE=INNODB DEFAULT CHARSET=latin1
        ');
    }

    public function safeDown()
    {
        echo "m190325_074444_store_transaction_flow_create cannot be reverted.\n";
        return false;
    }

}