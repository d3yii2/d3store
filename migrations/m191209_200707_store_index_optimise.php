<?php

use yii\db\Migration;

class m191209_200707_store_index_optimise  extends Migration {

    public function safeUp() {

        $this->execute('
            ALTER TABLE `store_transactions`   
              DROP INDEX `stack_to`,
              ADD  INDEX `stack_to` (`stack_to`, `remain_quantity`);
        ');

    }

    public function safeDown() {
        echo "m191209_200707_store_index_optimise cannot be reverted.\n";
        return false;
    }
}
