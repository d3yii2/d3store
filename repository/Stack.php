<?php
/**
 * Created by PhpStorm.
 * User: Uldis
 * Date: 2017.05.16.
 * Time: 5:20
 */

namespace d3yii2\d3store\repository;


use d3yii2\d3store\models\StoreStack;
use yii\helpers\ArrayHelper;

class Stack
{
    public static function getList(int $storeId, $full = true)
    {
        $conditions = ['store_id' => $storeId];
        if(!$full){
            $conditions['active'] = 1;
        }
        $stocks = StoreStack::find()
            ->select([
                'store_stack.id',
                'concat(store.name, " - ", store_stack.name) name'
            ])
            ->innerJoin('store_store store','store.id = store_stack.store_id')
            ->where(['store_id' => $storeId])
            ->orderBy('store.name, store_stack.name')
            ->all();
        return ArrayHelper::map($stocks, 'id', 'name');
    }
}