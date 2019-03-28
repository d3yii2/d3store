<?php

namespace d3yii2\d3store\models;

/**
 * This is the ActiveQuery class for [[StoreTransactionFlow]].
 *
 * @see StoreTransactionFlow
 */
class StoreTransactionFlowQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return StoreTransactionFlow[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return StoreTransactionFlow|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
    * @param string $fieldName
    * @param string $dateRange
    * @return d3yii2\d3store\models    */
    public function andFilterWhereDateRange(string $fieldName, $dateRange): self
    {
        if(empty($dateRange)){
            return $this;
        }

        $list = explode(' - ', $dateRange);
        if(count($list) !== 2){
            return $this;
        }

        return $this->andFilterWhere(['between', $fieldName, $list[0], $list[1]]);
    }
}
