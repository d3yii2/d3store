<?php

use yii\db\Migration;

class m251207_152831_d3yii2_d3store_tran_add_add_col_ref_record_id  extends Migration {

    public function safeUp() { 
        $this->execute('
            ALTER TABLE `store_tran_add`
              ADD COLUMN `ref_record_id` BIGINT UNSIGNED NULL AFTER `remain_quantity`;
            
                    
        ');
    }

    public function safeDown() {
        echo "m251207_152831_d3yii2_d3store_tran_add_add_col_ref_record_id cannot be reverted.\n";
        return false;
    }
}
