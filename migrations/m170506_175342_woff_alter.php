<?php

use yii\db\Migration;

class m170506_175342_woff_alter extends Migration
{
    public function up()
    {
        $sql = "
            ALTER TABLE `store_woff`   
              CHANGE `quantity` `quantity` DECIMAL(13,3) DEFAULT 3  NULL  COMMENT 'Quantity';
        ";
        $this->execute($sql);
    }

    public function down()
    {
        echo "m170506_175342_woff_alter cannot be reverted.\n";

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
