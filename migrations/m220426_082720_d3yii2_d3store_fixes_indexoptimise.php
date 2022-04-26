<?php

use yii\db\Migration;

class m220426_082720_d3yii2_d3store_fixes_indexoptimise  extends Migration {

    public function safeUp() { 
        $this->execute('
            ALTER TABLE 
              `store_fixes` DROP INDEX `store_fixes_ibfk_model`, 
              ADD KEY `store_fixes_ibfk_model` (`ref_model_id` , `ref_model_record_id`);         
        ');
    }

    public function safeDown() {
        echo "m220426_082720_d3yii2_d3store_ cannot be reverted.\n";
        return false;
    }
}
