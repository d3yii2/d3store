<?php

namespace d3yii2\d3store\repository;

use d3yii2\d3store\models\StoreTransactions;
use d3yii2\d3store\models\StoreWoff;


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
    public static function load($tranTime, $quantity, $stackToId, $refId, $refRecordId)
    {
        $transaction = new StoreTransactions();
        $transaction->action = StoreTransactions::ACTION_LOAD;
        $transaction->tran_time = $tranTime->format('Y-m-d H:i:s');
        $transaction->quantity = $quantity;
        $transaction->remain_quantity = $quantity;
        $transaction->stack_to = $stackToId;
        $transaction->ref_id = $refId;
        $transaction->ref_record_id = $refRecordId;

        if (!$transaction->save()) {
            throw new \Exception('Error:' . json_encode($transaction->errors));
        }

        return $transaction;

    }

    /**
     * @param \DateTime $tranTime
     * @param int $quantity
     * @param int $stackFromId
     * @param int $refId
     * @param int $refRecordId
     * @param int|bool $loadRefId
     * @param array $loadRefRecordIdList
     * @return StoreTransactions|bool
     * @throws \Exception
     */
    public static function unLoadFifo($tranTime, $quantity, $stackFromId, $refId, $refRecordId, $loadRefId = false, $loadRefRecordIdList = [])
    {
        if(!$transaction = self::unLoad($tranTime, $quantity, $stackFromId, $refId, $refRecordId, $loadRefId, $loadRefRecordIdList)){
            return false;
        }
        self::writeOff($transaction,'fifo', $loadRefId, $loadRefRecordIdList);
        return $transaction;
    }

    /**
     * @param \DateTime $tranTime
     * @param $quantity
     * @param $stackFromId
     * @param $refId
     * @param $refRecordId
     * @param array $loadRefRecordIdList
     * @return StoreTransactions|bool
     * @throws \Exception
     */
    public static function unLoadLifo($tranTime, $quantity, $stackFromId, $refId, $refRecordId, $loadRefId = false, $loadRefRecordIdList = [])
    {
        if(!$transaction = self::unLoad($tranTime, $quantity, $stackFromId, $refId, $refRecordId, $loadRefId, $loadRefRecordIdList)){
            return false;
        }
        self::writeOff($transaction,'lifo', $loadRefId, $loadRefRecordIdList);
        return $transaction;
    }

    /**
     * @param \DateTime $tranTime
     * @param $quantity
     * @param $stackFromId
     * @param $refId
     * @param $refRecordId
     * @return StoreTransactions|bool
     * @throws \Exception
     */
    private static function unLoad($tranTime, $quantity, $stackFromId, $refId,$refRecordId, $loadRefId = false, $loadRefRecordIdList = [])
    {
        $stackBalance = self::getStackBalance($stackFromId, $loadRefId, $loadRefRecordIdList);
        if($stackBalance < $quantity){
            return false;
        }
        $transaction = new StoreTransactions();
        $transaction->action = StoreTransactions::ACTION_UNLOAD;
        $transaction->tran_time = $tranTime->format('Y-m-d H:i:s');
        $transaction->quantity = $quantity;
        $transaction->remain_quantity = 0;
        $transaction->stack_from = $stackFromId;
        $transaction->ref_id = $refId;
        $transaction->ref_record_id = $refRecordId;

        if (!$transaction->save()) {
            throw new \Exception('Error:' . json_encode($transaction->errors));
        }

        return $transaction;

    }

    /**
     * @param \DateTime $tranTime
     * @param $quantity
     * @param $stackFromId
     * @param $stackToId
     * @return bool
     * @throws \Exception
     */
    public static function moveFifo($tranTime, $quantity, $stackFromId, $stackToId)
    {

        $remainTran = StoreTransactions::find()
            ->where(['stack_to' => $stackFromId])
            ->andFilterCompare('remain_quantity', 0, '>')
            ->orderBy(['tran_time' => SORT_DESC])
            ->all();
        $moveQuantity = $quantity;
        foreach ($remainTran as $rT) {

            $transaction = new StoreTransactions();
            $transaction->action = StoreTransactions::ACTION_MOVE;
            $transaction->tran_time = $tranTime->format('Y-m-d H:i:s');
            $transaction->remain_quantity = $quantity;
            $transaction->stack_from = $stackFromId;
            $transaction->stack_to = $stackToId;
            $transaction->ref_id = $rT->ref_id;
            $transaction->ref_record_id = $rT->ref_record_id;

            if ($moveQuantity > $rT->remain_quantity) {
                $moveQuantity -= $rT->remain_quantity;
                $transaction->quantity = $rT->remain_quantity;
                $transaction->remain_quantity = $rT->remain_quantity;
                $rT->remain_quantity = 0;
            } else {
                $rT->remain_quantity -= $moveQuantity;
                $transaction->quantity = $moveQuantity;
                $moveQuantity = 0;
            }
            if (!$rT->save()) {
                throw new \Exception('Error:' . json_encode($rT->errors));
            }
            if (!$transaction->save()) {
                throw new \Exception('Error:' . json_encode($transaction->errors));
            }
            if (0 === $moveQuantity) {
                break;
            }
        }
        return true;

    }

    /**
     * @param StoreTransactions $transaction
     * @param string $type
     * @param int|bool $loadRefId
     * @param array $loadRefRecordIdList
     * @return bool
     * @throws \Exception
     */
    private static function writeOff($transaction, $type, $loadRefId = false, $loadRefRecordIdList = [])
    {

        $sortDirection = $type === 'fifo' ? SORT_DESC : SORT_ASC;

        $query = StoreTransactions::find()
            ->where(['stack_to' => $transaction->stack_from])
            ->andFilterCompare('remain_quantity', 0, '>')
            ->orderBy(['tran_time' => $sortDirection]);
        if($loadRefId){
            $query->andWhere(['ref_id' => $loadRefId])
                ->andWhere(['ref_record_id' => $loadRefRecordIdList]);
        }

        $remainTran = $query->all();

        $woffQuantity = $transaction->quantity;
        foreach ($remainTran as $rT) {
            $woff = new StoreWoff();
            $woff->unload_tran_id = $transaction->id;
            $woff->load_tran_id = $rT->id;
            if ($woffQuantity > $rT->remain_quantity) {
                $woffQuantity -= $rT->remain_quantity;
                $woff->quantity = $rT->remain_quantity;
                $rT->remain_quantity = 0;
            } else {
                $rT->remain_quantity -= $woffQuantity;
                $woff->quantity = $woffQuantity;
                $woffQuantity = 0;
            }

            if (!$woff->save()) {
                throw new \Exception('Error:' . json_encode($woff->errors));
            }
            if (!$rT->save()) {
                throw new \Exception('Error:' . json_encode($rT->errors));
            }
            if (0 === $woffQuantity) {
                break;
            }
        }

        return true;

    }

    /**
     * @param int $stackId
     * @param int|bool $loadRefId
     * @param array $loadRefRecordIdList
     * @return array
     */
    public static function getStackBalance($stackId, $loadRefId = false, $loadRefRecordIdList = [])
    {
        $query = StoreTransactions::find()
            ->where(['stack_to' => $stackId]);
        if($loadRefId){
            $query->andWhere(['ref_id' => $loadRefId])
                ->andWhere(['ref_record_id' => $loadRefRecordIdList]);
        }
        return $query->sum('remain_quantity');
    }

    /**
     * @param int $storeId
     * @return array
     */
    public static function getAllStacksBalance($storeId)
    {
        return StoreTransactions::find()
            ->select([
                '`store_stack`.id id',
                'store_stack.name',
                'store_stack.type',
                'store_stack.product_name',
                'store_stack.capacity',
                'store_stack.notes',
                'sum(remain_quantity) remain_quantity'
            ])
            ->innerJoin('`store_stack`', '`store_stack`.id = store_transactions.stack_to')
            ->where(['store_id' => $storeId,'active' => 1])
            ->groupBy('`store_stack`.id')
            ->asArray()
            ->all();
    }
}