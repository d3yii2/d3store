<?php

use yii\db\Migration;

/**
* Class m190325_054920_transaction_add_ref*/
class m190325_054920_transaction_add_ref extends Migration
{
    /**
    * {@inheritdoc}
    */
    public function safeUp()
    {
        $this->execute('
            ALTER TABLE `store_transactions`   
              ADD COLUMN `add_ref_id` TINYINT UNSIGNED NULL AFTER `ref_record_id`,
              ADD COLUMN `add_ref_record_id` INT UNSIGNED NULL AFTER `add_ref_id`, 
              ADD  INDEX `add_ref` (`add_ref_id`, `add_ref_record_id`);
        ');
    }

    public function safeDown()
    {
        echo "m190325_054920_transaction_add_ref cannot be reverted.\n";
        return false;
    }

}