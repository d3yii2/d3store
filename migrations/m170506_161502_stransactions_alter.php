<?php

use yii\db\Migration;

class m170506_161502_stransactions_alter extends Migration
{
    public function up()
    {
        $sql = "
            ALTER TABLE `store_transactions`   
              CHANGE `remain_quantity` `remain_quantity` DECIMAL(13,3) NOT NULL  COMMENT 'Remain quantity';
        ";
        $this->execute($sql);
    }

    public function down()
    {
        echo "m170506_161502_stransactions_alter cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
