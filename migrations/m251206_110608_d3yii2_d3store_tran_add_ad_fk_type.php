<?php

use yii\db\Migration;

class m251206_110608_d3yii2_d3store_tran_add_ad_fk_type  extends Migration {

    public function safeUp() { 
        $this->execute('
            ALTER TABLE `store_tran_add`
              ADD CONSTRAINT `store_tran_add_ibfk_type` FOREIGN KEY (`type_id`) REFERENCES `store_tran_add_type` (`id`);
            
                    
        ');
    }

    public function safeDown() {
        echo "m251206_110608_d3yii2_d3store_tran_add_ad_fk_type cannot be reverted.\n";
        return false;
    }
}
