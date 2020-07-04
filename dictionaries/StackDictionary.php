<?php

namespace d3yii2\d3store\dictionaries;

use d3yii2\d3store\models\StoreStack;
use d3yii2\d3store\models\StoreStore;
use Yii;
use yii\helpers\ArrayHelper;

class StackDictionary{

    private const CACHE_KEY_LIST = 'StackDictionaryList';
    private const CACHE_KEY_LIST_STACK_NAME = 'StackDictionaryListStName';

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

    public static function getStackNameList(int $storeId = 0,bool $full = true): array
    {
        return Yii::$app->cache->getOrSet(
            self::createKeyStackNameList($storeId, $full),
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
                        'store_stack.name name'
                    ])
                    ->innerJoin('store_store store','store.id = store_stack.store_id')
                    ->where($conditions)
                    ->orderBy('store.name, store_stack.name')
                    ->all();
                return ArrayHelper::map($stocks, 'id', 'name');
            }
        );
    }

    public static function getCompanyList(array $storesIdList,bool $full = true): array
    {
        return Yii::$app->cache->getOrSet(
            self::createKeyCompany($storesIdList, $full),
            static function () use($storesIdList, $full) {
                $conditions = [
                    'store.id' => $storesIdList
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
    private static function createKeyStackNameList(int $storeId, bool $full): string
    {
        return self::CACHE_KEY_LIST_STACK_NAME . '-LIST-' . $storeId . '-' . ($full?'TRUE':'FALSE');
    }

    private static function createKeyCompany(array $companyIdList,bool $full): string
    {
        $key = self::CACHE_KEY_LIST . '-COMPANY-' . implode('=',$companyIdList) . '-' . ($full?'TRUE':'FALSE');
        $keys = self::getCompanyKeyList();
        if(!in_array($key,$keys, true)){
            $keys[] = $key;
            Yii::$app->cache->set(self::CACHE_KEY_LIST . '-COMPANY-KEYS',$keys);
        }
        return $key;
    }
    public static function clearCache(): void
    {
        foreach(StoreStore::find()->all() as $store) {
            Yii::$app->cache->delete(self::createKeyList(0, false));
            Yii::$app->cache->delete(self::createKeyList(0, true));
            Yii::$app->cache->delete(self::createKeyList($store->id, false));
            Yii::$app->cache->delete(self::createKeyList($store->id, true));
            Yii::$app->cache->delete(self::createKeyStackNameList(0, false));
            Yii::$app->cache->delete(self::createKeyStackNameList(0, true));
            Yii::$app->cache->delete(self::createKeyStackNameList($store->id, false));
            Yii::$app->cache->delete(self::createKeyStackNameList($store->id, true));

        }
        foreach(self::getCompanyKeyList() as $key){
            Yii::$app->cache->delete($key);
        }
    }

    /**
     * @return array|mixed
     */
    private static function getCompanyKeyList()
    {
        if (!$keys = Yii::$app->cache->get(self::CACHE_KEY_LIST . '-COMPANY-KEYS')) {
            $keys = [];
        }
        return $keys;
    }
}
