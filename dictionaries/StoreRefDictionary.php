<?php

namespace d3yii2\d3store\dictionaries;

use Yii;
use d3yii2\d3store\models\StoreRef;
use yii\helpers\ArrayHelper;

class StoreRefDictionary
{

    private const CACHE_KEY_LIST = 'StoreRefDictionaryList';


    /**
    * @return string[]
    */
    public static function getList(): array
    {
        return Yii::$app->cache->getOrSet(
            self::CACHE_KEY_LIST,
            static function () {
                return ArrayHelper::map(
                    StoreRef::find()
                        ->select([
                            'id' => '`store_ref`.`id`' ,
                            'name' => '`store_ref`.`name`',
                        ])
                        ->orderBy([
                            '`store_ref`.`name`' => SORT_ASC,
                        ])
                        ->asArray()
                        ->all(),
                    'id',
                    'name'
                );
            },
            60 * 60
        );
    }


    /**
    * get label
    * @param int $id
    * @return string|null
    */
    public static function getLabel(int $id): ?string
    {
        return self::getList()[$id]??null;
    }

    public static function clearCache(): void
    {
        Yii::$app->cache->delete(self::CACHE_KEY_LIST);
    }
}
