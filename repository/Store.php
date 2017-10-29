<?php


namespace d3yii2\d3store\repository;


use d3yii2\d3store\models\StoreStore;

class Store
{
    public static function getCompanyStores(int $companyId)
    {
        $stores = StoreStore::find()
            ->select('id')
            ->where(['company_id' => $companyId])
            ->column();

        return $stores;
    }
}