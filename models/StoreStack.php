<?php

namespace d3yii2\d3store\models;

use d3yii2\d3store\dictionaries\StackDictionary;
use \d3yii2\d3store\models\base\StoreStack as BaseStoreStack;
use DateInterval;
use DateTime;

/**
 * This is the model class for table "store_stack".
 */
class StoreStack extends BaseStoreStack
{
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        StackDictionary::clearCache();
    }

    public function afterDelete()
    {
        parent::afterDelete();
        StackDictionary::clearCache();
    }

    public function balance(string $time = ''): float
    {
        $query = StoreTransactions::find()
            ->select([
                'q' => 'IFNULL(SUM(remain_quantity),0)'])
            ->where(['stack_to' => $this->id]);
        if($time){
            if(strlen($time) === 10){
                $time .= ' 00:00:00';
            }
            $dateTime = DateTime::createFromFormat('Y-m-d H:i:s',$time);
            $dateTime->add(new DateInterval('P1D'));

            $query->andWhere([
                '<=',
                'tran_time',
                $dateTime->format('Y-m-d H:i:s')
            ]);
        }
        return $query->scalar();
    }
}
