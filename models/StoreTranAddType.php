<?php

namespace d3yii2\d3store\models;

use d3yii2\d3store\dictionaries\StoreTranAddTypeDictionary;
use d3yii2\d3store\models\base\StoreTranAddType as BaseStoreTranAddType;

/**
 * This is the model class for table "store_tran_add_type".
 */
class StoreTranAddType extends BaseStoreTranAddType
{


    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);
        StoreTranAddTypeDictionary::clearCache();
    }

    public function afterDelete(): void
    {
        parent::afterDelete();
        StoreTranAddTypeDictionary::clearCache();
    }

    public static function optsStoreTranAddDictionary(): array
    {
        return StoreTranAddTypeDictionary::getCodeList();
    }
}
