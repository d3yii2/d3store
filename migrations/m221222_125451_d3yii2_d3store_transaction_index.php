<?php

use yii\db\Migration;

class m221222_125451_d3yii2_d3store_transaction_index  extends Migration {

    public function safeUp() { 
        $this->execute('
            ALTER TABLE `store_transactions`
              DROP INDEX `ref_id`,
              ADD KEY `ref_id` (
                `ref_id`,
                `action`,
                `ref_record_id`
              );        
        ');
    }

    public function safeDown() {
        echo "m221222_125451_d3yii2_d3store_transaction_index cannot be reverted.\n";
        return false;
    }
}
