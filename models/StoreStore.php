<?php

namespace d3yii2\d3store\models;

use d3yii2\d3store\dictionaries\StackDictionary;
use d3yii2\d3store\dictionaries\StoreDictionary;
use \d3yii2\d3store\models\base\StoreStore as BaseStoreStore;

/**
 * This is the model class for table "store_store".
 */
class StoreStore extends BaseStoreStore
{
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        StackDictionary::clearCache();
        StoreDictionary::clearCache();
    }

    public function afterDelete()
    {
        parent::afterDelete();
        StackDictionary::clearCache();
        StoreDictionary::clearCache();
    }
}
