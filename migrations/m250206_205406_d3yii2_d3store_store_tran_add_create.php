<?php

use yii\db\Migration;

class m250206_205406_d3yii2_d3store_store_tran_add_create  extends Migration {

    public function safeUp() { 
        $this->execute('
            CREATE TABLE `store_tran_add` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `transactions_id` INT UNSIGNED NOT NULL,
              `type_id` TINYINT UNSIGNED NOT NULL,
              `quantity` DECIMAL (13, 3) NOT NULL,
              `remain_quantity` DECIMAL (13, 3) NOT NULL,
              PRIMARY KEY (`id`),
              FOREIGN KEY (`transactions_id`) REFERENCES `store_transactions` (`id`)
            );
            
                    
        ');
    }

    public function safeDown() {
        echo "m250206_205406_d3yii2_d3store_store_tran_add_create cannot be reverted.\n";
        return false;
    }
}
