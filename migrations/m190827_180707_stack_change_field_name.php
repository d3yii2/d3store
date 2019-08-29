<?php

use yii\db\Migration;

class m190827_180707_stack_change_field_name  extends Migration {

    public function safeUp() {

        $this->execute('
            ALTER TABLE `store_stack`   
              CHANGE `name` `name` VARCHAR(255) CHARSET utf8 COLLATE utf8_general_ci NULL  COMMENT \'Stack name\';

        ');

    }

    public function safeDown() {
        echo "m190827_180707_stack_change_field_name cannot be reverted.\n";
        return false;
    }
}
