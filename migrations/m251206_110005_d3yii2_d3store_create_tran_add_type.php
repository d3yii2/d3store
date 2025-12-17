<?php

use yii\db\Migration;

class m251206_110005_d3yii2_d3store_create_tran_add_type  extends Migration {

    public function safeUp() { 
        $this->execute('
            CREATE TABLE `store_tran_add_type` (
              `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `name` VARCHAR (30) CHARSET utf8mb3 NOT NULL COMMENT \'Add type name\',
              `code` VARCHAR(30) NOT NULL COMMENT \'Add type code\',
              PRIMARY KEY (`id`)
            );
        ');

        $this->execute('
            INSERT INTO store_tran_add_type
            SELECT DISTINCT
              type_id,
              type_id,
              type_id 
            FROM `store_tran_add`        
        ');
    }

    public function safeDown() {
        echo "m251206_110005_d3yii2_d3store_create_tran_add_type cannot be reverted.\n";
        return false;
    }
}
