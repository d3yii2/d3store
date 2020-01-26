<?php

namespace d3yii2\d3store\dictionaries;

use d3yii2\d3store\models\StoreStore;
use Yii;
use yii\helpers\ArrayHelper;

class StoreDictionary
{

    private const CACHE_KEY_LIST = 'StoreDictionaryList';

    public static function getList(int $companyId): array
    {
        return Yii::$app->cache->getOrSet(
            self::createKey($companyId),
            static function () use ($companyId) {
                return ArrayHelper::map(
                    StoreStore::find()
                        ->select([
                            'id' => 'id',
                            'name' => 'name',
                        ])
                        ->where(['company_id' => $companyId])
                        ->orderBy([
                            'name' => SORT_ASC,
                        ])
                        ->asArray()
                        ->all()
                    ,
                    'id',
                    'name'
                );
            }
        );
    }

    private static function createKey(int $companyId): string
    {
        return self::CACHE_KEY_LIST . ':' . $companyId;
    }

    public static function clearCache(): void
    {
        foreach (
            StoreStore::find()
                ->select(['company_id'])
                ->distinct()
                ->asArray()
                ->column() as $companyId
        ) {
            Yii::$app->cache->delete(self::createKey($companyId));
        }
    }
}
