<?php

namespace d3yii2\d3store\models;


use d3yii2\d3store\dictionaries\StoreRefDictionary;
use d3yii2\d3store\models\base\StoreRef as BaseStoreRef;


/**
 * This is the model class for table "store_ref".
 */
class StoreRef extends BaseStoreRef
{

    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);
        StoreRefDictionary::clearCache();
    }

    public function afterDelete(): void
    {
        parent::afterDelete();
        StoreRefDictionary::clearCache();
    }

    public static function optsUnit(): array
    {
        return StoreRefDictionary::getList();
    }

}
