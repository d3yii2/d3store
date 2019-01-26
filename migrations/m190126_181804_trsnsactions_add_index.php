<?php

use yii\db\Migration;

/**
 * Class m190126_181804_trsnsactions_add_index
 */
class m190126_181804_trsnsactions_add_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('
            ALTER TABLE `store_transactions`   
              ADD  INDEX `ref_record_id` (`ref_record_id`, `ref_id`);
        ');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('
            ALTER TABLE `store_transactions`   
              DROP INDEX `ref_record_id`;

        ');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190126_181804_trsnsactions_add_index cannot be reverted.\n";

        return false;
    }
    */
}
