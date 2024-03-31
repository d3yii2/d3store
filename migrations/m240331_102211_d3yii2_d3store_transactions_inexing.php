<?php

use yii\db\Migration;

class m240331_102211_d3yii2_d3store_transactions_inexing  extends Migration {

    public function safeUp() { 
        $this->execute('
            ALTER TABLE .`store_transactions`
              DROP INDEX `ref_record_id`,
              ADD KEY `ref_record_id` (
                `ref_record_id`,
                `ref_id`,
                `remain_quantity`
              );
            
                    
        ');
    }

    public function safeDown() {
        echo "m240331_102211_d3yii2_d3store_transactions_inexing cannot be reverted.\n";
        return false;
    }
}
