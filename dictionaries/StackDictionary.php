<?php

namespace d3yii2\d3store\dictionaries;

use d3yii2\d3store\models\StoreStack;
use d3yii2\d3store\models\StoreStore;
use Yii;
use yii\helpers\ArrayHelper;

class StackDictionary{

    private const CACHE_KEY_LIST = 'StackDictionaryList';

    public static function getList(int $storeId = 0,bool $full = true): array
    {
        return Yii::$app->cache->getOrSet(
            self::createKeyList($storeId, $full),
            static function () use($storeId, $full) {
                $conditions = [];
                if($storeId) {
                    $conditions = [
                        'store_id' => $storeId
                    ];
                    if (!$full) {
                        $conditions['store_stack.active'] = 1;
                    }
                }
                $stocks = StoreStack::find()
                    ->select([
                        'store_stack.id',
                        'concat(store.name, " - ", store_stack.name) name'
                    ])
                    ->innerJoin('store_store store','store.id = store_stack.store_id')
                    ->where($conditions)
                    ->orderBy('store.name, store_stack.name')
                    ->all();
                return ArrayHelper::map($stocks, 'id', 'name');
            }
        );
    }

    public static function getCompanyList(int $companyId,bool $full = true): array
    {
        return Yii::$app->cache->getOrSet(
            self::createKeyCompany($companyId, $full),
            static function () use($companyId, $full) {
                $conditions = [
                    'company_id' => $companyId
                ];
                if (!$full) {
                    $conditions['store_stack.active'] = 1;
                }
                $stocks = StoreStack::find()
                    ->select([
                        'store_stack.id',
                        'concat(store.name, " - ", store_stack.name) name'
                    ])
                    ->innerJoin('store_store store', 'store.id = store_stack.store_id')
                    ->where($conditions)
                    ->orderBy('store.name, store_stack.name')
                    ->all();
                return ArrayHelper::map($stocks, 'id', 'name');
            }
        );
    }

    private static function createKeyList(int $storeId, bool $full): string
    {
        return self::CACHE_KEY_LIST . '-LIST-' . $storeId . '-' . ($full?'TRUE':'FALSE');
    }

    private static function createKeyCompany(int $companyId,bool $full): string
    {
        return self::CACHE_KEY_LIST . '-COMPANY-' . $companyId . '-' . ($full?'TRUE':'FALSE');
    }
    public static function clearCache(): void
    {
        foreach(StoreStore::find()->all() as $store) {
            foreach([false,true] as $full) {
                Yii::$app->cache->delete(self::createKeyList($store->id, $full));
                Yii::$app->cache->delete(self::createKeyCompany($store->company_id, $full));
            }
        }
    }
}
