<?php

namespace d3yii2\d3store\models;

/**
 * This is the ActiveQuery class for [[\d3yii2\d3store\models\StoreStore]].
 *
 * @see \d3yii2\d3store\models\StoreStore
 */
class StoreStoreQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return \d3yii2\d3store\models\StoreStore[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \d3yii2\d3store\models\StoreStore|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
