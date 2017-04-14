<?php

namespace d3yii2\d3store\repository;

use d3yii2\d3store\models\StoreTransactions;


class Transactions
{

    /**
     * @param \DateTime $tranTime
     * @param $quantity
     * @param $stackToId
     * @param $refId
     * @param $refRecordId
     * @return StoreTransactions
     * @throws \Exception
     */
    public static  function load($tranTime, $quantity, $stackToId, $refId, $refRecordId)
    {
        $transaction = new StoreTransactions();
        $transaction->action = StoreTransactions::ACTION_LOAD;
        $transaction->tran_time = $tranTime->format('Y-m-d H:i:s');
        $transaction->quantaty = $quantity;
        $transaction->remain_quantity = $quantity;
        $transaction->stack_to = $stackToId;
        $transaction->ref_id = $refId;
        $transaction->ref_record_id = $refRecordId;

        if(!$transaction->save()){
            throw new \Exception('Error:' . json_encode($transaction->errors));
        }

        return $transaction;

    }

    /**
     * @param \DateTime $tranTime
     * @param $quantity
     * @param $stackFromId
     * @param $refId
     * @param $refRecordId
     * @return StoreTransactions
     * @throws \Exception
     */
    public static  function unLoad($tranTime, $quantity, $stackFromId, $refId, $refRecordId)
    {
        $transaction = new StoreTransactions();
        $transaction->action = StoreTransactions::ACTION_UNLOAD;
        $transaction->tran_time = $tranTime->format('Y-m-d H:i:s');
        $transaction->quantaty = $quantity;
        $transaction->remain_quantity = $quantity;
        $transaction->stack_from = $stackFromId;
        $transaction->ref_id = $refId;
        $transaction->ref_record_id = $refRecordId;

        if(!$transaction->save()){
            throw new \Exception('Error:' . json_encode($transaction->errors));
        }

        return $transaction;

    }

    /**
     * @param \DateTime $tranTime
     * @param $quantity
     * @param $stackFromId
     * @param $stackToId
     * @return StoreTransactions
     * @throws \Exception
     */
    public static  function move($tranTime, $quantity, $stackFromId, $stackToId)
    {
        $transaction = new StoreTransactions();
        $transaction->action = StoreTransactions::ACTION_MOVE;
        $transaction->tran_time = $tranTime->format('Y-m-d H:i:s');
        $transaction->quantaty = $quantity;
        $transaction->remain_quantity = $quantity;
        $transaction->stack_from = $stackFromId;
        $transaction->stack_to = $stackToId;

        if(!$transaction->save()){
            throw new \Exception('Error:' . json_encode($transaction->errors));
        }

        return $transaction;

    }

    /**
     * @param StoreTransactions $transaction
     * @return bool
     */
    public static function writeOffFifo($transaction)
    {
        return self::writeOff($transaction, 'fifo');
    }

    /**
     * @param StoreTransactions $transaction
     * @return bool
     */
    public static function writeOffLifo($transaction)
    {
        return self::writeOff($transaction, 'lifo');
    }

    /**
     * @param StoreTransactions $transaction
     * @param string $type
     * @return bool
     */
    public static function writeOff($transaction, $type){

        $sortDirection = $type === 'fifo'?SORT_DESC:SORT_ASC;

        $remainTran = StoreTransactions::find()
            ->where(['stack_to'=>$transaction->stack_from])
            ->andFilterCompare('remain_quantity',0,'>')
            ->orderBy(['tran_time' => $sortDirection])
            ->all();

        $woffQuantity = $transaction->quantaty;
        foreach($remainTran as $rT){
            if($woffQuantity > $rT->remain_quantity){
                $woffQuantity -= $rT->remain_quantity;
                $rT->remain_quantity = 0;
            }else{
                $rT->remain_quantity -= $woffQuantity;
                $woffQuantity = 0;
            }
            $rT->save();
            if(0 ===$woffQuantity){
                break;
            }
        }

        return true;

    }

    /**
     * @param int $stackId
     * @return mixed
     */
    public static function getStackBalance($stackId)
    {
        return StoreTransactions::find()
            ->where(['stack_to' => $stackId])
            ->sum('remain_quantity');
    }
}