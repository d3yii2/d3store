<?php

namespace d3yii2\d3store\models;

use d3yii2\d3store\dictionaries\StackDictionary;
use \d3yii2\d3store\models\base\StoreStack as BaseStoreStack;

/**
 * This is the model class for table "store_stack".
 */
class StoreStack extends BaseStoreStack
{
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        StackDictionary::clearCache();
    }

    public function afterDelete()
    {
        parent::afterDelete();
        StackDictionary::clearCache();
    }

    public function balance(): float
    {
        return StoreTransactions::find()
            ->where(['stack_to' => $this->id])
            ->sum('remain_quantity');
    }
}
