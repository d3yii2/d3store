<?php

namespace d3yii2\d3store\dictionaries;

use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3store\models\StoreStore;
use Yii;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

class StoreDictionary
{

    private const CACHE_KEY_LIST = 'StoreDictionaryList';

    public static function getListExtended(int $companyId, array $storesIds)
    {
        $list = self::getList($companyId);
        foreach ($list as $k => $v) {
            if (!in_array($k,$storesIds, false)) {
                unset($list[$k]);
            }
        }
        return $list;
    }

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

    /**
     * @throws Exception
     * @throws D3ActiveRecordException
     */
    public static function getStoreIdByName(int $companyId, string $name): int
    {
        if ($id =array_search($name,self::getList($companyId),true)) {
            return $id;
        }
        $model = new StoreStore();
        $model->company_id = $companyId;
        $model->name = $name;
        if (!$model->save()) {
            throw new D3ActiveRecordException($model);
        }
        return $model->id;
    }
}
