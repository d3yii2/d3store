<?php

namespace d3yii2\d3store\models;

use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3store\dictionaries\StoreTranAddTypeDictionary;
use d3yii2\d3store\models\base\StoreTranAddType as BaseStoreTranAddType;
use yii\db\Exception;

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

    /**
     * use for migration
     * @param array $list like ['code1' => 'Type name 1' , 'code2' => 'Type name 2' ]
     * if Transaction additional type already exist, ignore it
     * @throws Exception
     * @throws D3ActiveRecordException
     */
    public static function migrate(array $list): void
    {
        foreach ($list as $code => $name) {
            if (self::findOne(['code' => $code])) {
                continue;
            }
            $model = new StoreTranAddType([
                'code' => $code,
                'name' => $name
            ]);
            if (!$model->save()) {
                throw new D3ActiveRecordException($model);
            }
        }
        StoreTranAddTypeDictionary::clearCache();
    }
}
