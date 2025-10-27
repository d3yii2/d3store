<?php

namespace d3yii2\d3store\dictionaries;

use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3store\models\StoreStack;
use d3yii2\d3store\models\StoreStore;
use Yii;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * stack dictionary
 */
class StackDictionary
{
    private const CACHE_KEY_LIST = 'StackDictionaryList';
    private const CACHE_KEY_LIST_STACK_NAME = 'StackDictionaryListStName';
    private const CACHE_KEY_LIST_STORE_NAME = 'StackDictionaryStoreNameList';

    public static function getList(int $storeId = 0, bool $full = true): array
    {
        return Yii::$app->cache->getOrSet(
            self::createKeyList($storeId, $full),
            static function () use ($storeId, $full) {
                $conditions = [];
                if ($storeId) {
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
                    ->innerJoin('store_store store', 'store.id = store_stack.store_id')
                    ->where($conditions)
                    ->orderBy('store.name, store_stack.name')
                    ->all();
                return ArrayHelper::map($stocks, 'id', 'name');
            }
        );
    }

    public static function getId2StoreNameList(): array
    {
        return Yii::$app->cache->getOrSet(
            self::CACHE_KEY_LIST_STORE_NAME,
            static function () {
                $list = StoreStack::find()
                    ->select([
                        'store_stack.id',
                        'store.name'
                    ])
                    ->innerJoin('store_store store', 'store.id = store_stack.store_id')
                    ->all();
                return ArrayHelper::map($list, 'id', 'name');
            }
        );
    }

    public static function getStackNameList(int $storeId = 0, bool $full = true): array
    {
        return Yii::$app->cache->getOrSet(
            self::createKeyStackNameList($storeId, $full),
            static function () use ($storeId, $full) {
                $conditions = [];
                if ($storeId) {
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
                    ->innerJoin('store_store store', 'store.id = store_stack.store_id')
                    ->where($conditions)
                    ->orderBy('store.name, store_stack.name')
                    ->all();
                return ArrayHelper::map($stocks, 'id', 'name');
            }
        );
    }

    public static function getCompanyStackList(int $companyId = null, bool $full = true): array
    {
        return Yii::$app->cache->getOrSet(
            self::createKeyCompanyStackList($companyId, $full),
            static function () use ($companyId, $full) {
                $conditions = [];
                if ($companyId) {
                    $conditions['store.company_id'] = $companyId;
                }
                if (!$full) {
                    $conditions['store_stack.active'] = 1;
                }
                $stocks = StoreStack::find()
                    ->select([
                        'store_stack.id',
                        'concat(store.name, " - ", store_stack.name) name'
                    ])
                    ->innerJoin(
                        'store_store store',
                        'store.id = store_stack.store_id'
                    )
                    ->where($conditions)
                    ->orderBy('store.name, store_stack.name')
                    ->asArray()
                    ->all();
                return ArrayHelper::map($stocks, 'id', 'name');
            }
        );
    }

    /**
     * @param int|null $companyId
     * @param bool $full
     * @return string
     */
    private static function createKeyCompanyStackList(int $companyId = null, bool $full = true): string
    {
        if (!$companyId) {
            $companyId = 0;
        }
        return 'StoreCompanyStackList' . $companyId . '-' . $full;
    }

    public static function getCompanyList(array $storesIdList, bool $full = true): array
    {
        return Yii::$app->cache->getOrSet(
            self::createKeyCompany($storesIdList, $full),
            static function () use ($storesIdList, $full) {
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

    private static function createKeyCompany(array $companyIdList, bool $full): string
    {
        $key = self::CACHE_KEY_LIST . '-COMPANY-' . implode('=', $companyIdList) . '-' . ($full?'TRUE':'FALSE');
        $keys = self::getCompanyKeyList();
        if (!in_array($key, $keys, true)) {
            $keys[] = $key;
            Yii::$app->cache->set(self::CACHE_KEY_LIST . '-COMPANY-KEYS', $keys);
        }
        return $key;
    }
    public static function clearCache(): void
    {
        Yii::$app->cache->delete(self::CACHE_KEY_LIST_STORE_NAME);
        foreach (StoreStore::find()->all() as $store) {
            Yii::$app->cache->delete(self::createKeyList(0, false));
            Yii::$app->cache->delete(self::createKeyList(0, true));
            Yii::$app->cache->delete(self::createKeyList($store->id, false));
            Yii::$app->cache->delete(self::createKeyList($store->id, true));
            Yii::$app->cache->delete(self::createKeyStackNameList(0, false));
            Yii::$app->cache->delete(self::createKeyStackNameList(0, true));
            Yii::$app->cache->delete(self::createKeyStackNameList($store->id, false));
            Yii::$app->cache->delete(self::createKeyStackNameList($store->id, true));
        }
        foreach (self::getCompanyKeyList() as $key) {
            Yii::$app->cache->delete($key);
        }
        foreach (StoreStore::find()
            ->distinct()
            ->select('company_id')
            ->column() as $companyId) {
            Yii::$app->cache->delete(self::createKeyCompanyStackList($companyId, false));
            Yii::$app->cache->delete(self::createKeyCompanyStackList($companyId));
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



    /**
     * find or create stack by keys
     * @param int $storeId
     * @param string $stackGroupCode unique code for stack group
     * @param array $stackKeys list keys, what identify stack
     * @return StoreStack
     */
    public static function findGroupStack(
        int $storeId,
        string $stackGroupCode,
        array $stackKeys
    ): ?StoreStack {
        $sysName = self::createSysName($stackGroupCode, $stackKeys);
        return StoreStack::findOne([
            'store_id' => $storeId,
            'sys_name' => $sysName
        ]);
    }

    public static function findGroupStackList(
        string $stackGroupCode,
        array $stackKeys
    ): array {
        $sysName = self::createSysName($stackGroupCode, $stackKeys);
        return ArrayHelper::map(
            StoreStack::find()
                ->select([
                    'id',
                    'name'
                ])
                ->where([
                    'sys_name' => $sysName
                ])
                ->asArray()
                ->all(),
            'id',
            'name'
        );
    }

    /**
     * create stack
     * @param int $storeId
     * @param string $stackGroupCode
     * @param array $stackKeys
     * @param string $stackName
     * @return StoreStack
     * @throws D3ActiveRecordException
     * @throws Exception
     */
    public static function createGroupStack(
        int $storeId,
        string $stackGroupCode,
        array $stackKeys,
        string $stackName
    ): StoreStack {
        $sysName = self::createSysName($stackGroupCode, $stackKeys);
        $stack = new StoreStack();
        $stack->store_id = $storeId;
        $stack->name = $stackName;
        $stack->setTypeStandard();
        $stack->active = 1;
        $stack->sys_name = $sysName;
        if (!$stack->save()) {
            throw new D3ActiveRecordException($stack);
        }
        return $stack;
    }

    /**
     * @param string $stackGroupCode
     * @param array $stackKeys
     * @return string
     */
    private static function createSysName(string $stackGroupCode, array $stackKeys): string
    {
        return $stackGroupCode . '-' . implode('-', $stackKeys);
    }

    /**
     * @throws Exception
     * @throws D3ActiveRecordException
     */
    public static function getOrCreateId(int $storeId, string $name): int
    {
        if (false !== ($id = array_search($name, self::getStackNameList($storeId), true))) {
            return $id;
        }
        $stack = new StoreStack();
        $stack->store_id = $storeId;
        $stack->name = $name;
        $stack->type = StoreStack::TYPE_STANDARD;
        $stack->active = 1;
        if (!$stack->save()) {
            throw new D3ActiveRecordException($stack);
        }
        return $stack->id;
    }
}
