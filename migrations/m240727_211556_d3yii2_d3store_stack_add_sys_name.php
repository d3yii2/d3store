<?php

use yii\db\Migration;

class m240727_211556_d3yii2_d3store_stack_add_sys_name  extends Migration {

    public function safeUp() { 
        $this->execute('
            ALTER TABLE `store_stack`
              ADD COLUMN `sys_name` VARCHAR (50) CHARSET utf8 NULL AFTER `active`;
                    
        ');
    }

    public function safeDown() {
        echo "m240727_211556_d3yii2_d3store_stack_add_sys_name cannot be reverted.\n";
        return false;
    }
}
