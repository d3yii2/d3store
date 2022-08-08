<?php

namespace d3yii2\d3store\dictionaries;

use d3yii2\d3store\models\StoreStore;
use Yii;
use yii\helpers\ArrayHelper;

class StoreDictionary
{

    private const CACHE_KEY_LIST = 'StoreDictionaryList';

    public static function getList(int $companyId = null): array
    {
        return Yii::$app->cache->getOrSet(
            self::createKey($companyId),
            static function () use ($companyId) {
                $storeStoreQuery = StoreStore::find()
                    ->select([
                        'id' => 'id',
                        'name' => 'name',
                    ])
                    ->orderBy([
                        'name' => SORT_ASC,
                    ]);
                if ($companyId) {
                    $storeStoreQuery
                        ->where(['company_id' => $companyId]);
                }
                return ArrayHelper::map(
                    $storeStoreQuery
                        ->asArray()
                        ->all(),
                    'id',
                    'name'
                );
            }
        );
    }

    private static function createKey(int $companyId = null): string
    {
        if (!$companyId) {
            $companyId = 0;
        }
        return self::CACHE_KEY_LIST . ':' . $companyId;
    }

    public static function clearCache(): void
    {
        Yii::$app->cache->delete(self::createKey(null));
        foreach (StoreStore::find()
                ->select(['company_id'])
                ->distinct()
                ->column() as $companyId
        ) {
            Yii::$app->cache->delete(self::createKey($companyId));
        }
    }
}
