<?php

namespace d3yii2\d3store\models;

use d3yii2\d3store\dictionaries\StackDictionary;
use d3yii2\d3store\dictionaries\StoreDictionary;
use d3yii2\d3store\models\base\StoreStack as BaseStoreStack;
use DateInterval;
use DateTime;
use Yii;
use yii\base\UserException;

/**
 * This is the model class for table "store_stack".
 */
class StoreStack extends BaseStoreStack
{
    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);
        StackDictionary::clearCache();
        StoreDictionary::clearCache();
    }

    public function delete(): void
    {
        if (StoreTransactions::find()->where(['stack_to' => $this->id])->exists()
            || StoreTransactions::find()->where(['stack_from' => $this->id])->exists()
        ) {
            throw new UserException(Yii::t('d3store', 'Stack is used and cannot be deleted'));
        }
        parent::delete();
    }

    public function afterDelete(): void
    {
        parent::afterDelete();
        StackDictionary::clearCache();
        StoreDictionary::clearCache();
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
