<?php

namespace d3yii2\d3store\models;

/**
 * This is the ActiveQuery class for [[\d3yii2\d3store\models\StoreStack]].
 *
 * @see \d3yii2\d3store\models\StoreStack
 */
class StoreStackQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return \d3yii2\d3store\models\StoreStack[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \d3yii2\d3store\models\StoreStack|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
