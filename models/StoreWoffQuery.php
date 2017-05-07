<?php

namespace d3yii2\d3store\models;

/**
 * This is the ActiveQuery class for [[\d3yii2\d3store\models\StoreWoff]].
 *
 * @see \d3yii2\d3store\models\StoreWoff
 */
class StoreWoffQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return \d3yii2\d3store\models\StoreWoff[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \d3yii2\d3store\models\StoreWoff|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
