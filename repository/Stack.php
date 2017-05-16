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
    public static function getList($stackId, $full = true)
    {
        $conditions = ['store_id' => $stackId];
        if(!$full){
            $conditions['active'] = 1;
        }
        $stocks = StoreStack::find()
            ->where(['store_id' => $stackId])
            ->orderBy('name')
            ->all();
        return ArrayHelper::map($stocks, 'id', 'name');
    }
}