<?php

namespace d3yii2\d3store\repository;

class Stack
{
    /**
     * @param int $storeId
     * @param bool $full
     * @return array
     */
    public static function getList(int $storeId = 0, $full = true): array
    {
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
    public static function getCompanyList(int $companyId, $full = true): array
    {
        $conditions = [
            'company_id' => $companyId
        ];
        if(!$full){
            $conditions['store_stack.active'] = 1;
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
}
