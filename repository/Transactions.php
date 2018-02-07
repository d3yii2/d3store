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

    public static function deleteLoad($refId, $refRecordId)
    {
        $tran = StoreTransactions::findOne([
            'action' => StoreTransactions::ACTION_LOAD,
            'ref_id' => $refId,
            'ref_record_id' =>  $refRecordId,
        ]);

        if($tran->getStoreWoffs()->one()){
            throw new \Exception('Can not delete transactin. Transaction has write off');
        }

        if(!$tran->delete()){
            throw new \Exception('Can not delete transactin.' . json_encode($tran->getErrors()));
        }

        return true;

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
            $tranQuantity = $rT->remain_quantity;
            if($tranQuantity > $moveQuantity){
                $tranQuantity = $moveQuantity;
            }
            $moveQuantity -= $tranQuantity;
            self::moveTransaction($tranTime, $stackToId, $rT, $tranQuantity);
            if ($moveQuantity < $quantity/1000000000) {
                break;
            }
        }
        return true;

    }

    /**
     * @param \DateTime $tranTime
     * @param int $stackToId
     * @param StoreTransactions $rT
     * @param float $tranQuantity
     * @param int $refId
     * @param int $refRecordId
     * @return StoreTransactions
     * @throws \Exception
     */
    public static function moveTransaction(
        \DateTime $tranTime,
        int $stackToId,
        StoreTransactions $rT,
        float $tranQuantity,
        int $refId = 0,
        int $refRecordId = 0
    ): StoreTransactions
    {
        if(!$refId){
            $refId = $rT->ref_id;
        }

        if(!$refRecordId){
            $refRecordId = $rT->ref_record_id;
        }

        $transaction = new StoreTransactions();
        $transaction->action = StoreTransactions::ACTION_MOVE;
        $transaction->tran_time = $tranTime->format('Y-m-d H:i:s');
        $transaction->stack_from = $rT->stack_to;
        $transaction->stack_to = $stackToId;
        $transaction->ref_id = $refId;
        $transaction->ref_record_id = $refRecordId;
        $transaction->quantity = $tranQuantity;
        $transaction->remain_quantity = $tranQuantity;

        $rT->remain_quantity -= $tranQuantity;

        if (!$rT->save()) {
            throw new \Exception('Error:' . json_encode($rT->errors));
        }
        if (!$transaction->save()) {
            throw new \Exception('Error:' . json_encode($transaction->errors));
        }
        return $transaction;
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
            if ($woffQuantity < $transaction->quantity/1000000000) {
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
    public static function getAllStacksBalance()
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
            ->innerJoin('`store_store`', 'store_store.id = `store_stack`.store_id')
            ->where([
                'store_store.company_id' => \Yii::$app->SysCmp->getActiveCompanyId(),
                'store_stack.active' => 1
            ])
            ->groupBy('`store_stack`.id')
            ->asArray()
            ->all();
    }

    /**
     * @param int $unLoadTranId
     * @return int[] array
     * @throws \Exception
     */
    public static function deleteUnload($unLoadTranId)
    {
        $unloadTransaction = StoreTransactions::findOne($unLoadTranId);

        $loadTranIdList = [];
        /** @var StoreWoff $woff */
        foreach(StoreWoff::findAll(['unload_tran_id' => $unLoadTranId]) as $woff){
            /** @var StoreTransactions $loadTran */
            $loadTran = $woff->getLoadTran()->one();
            $loadTranIdList[] = $loadTran->id;
            $loadTran->remain_quantity += $woff->quantity;
            if(!$loadTran->save()){
                throw new \Exception('Errror:' . json_encode($loadTran->getErrors()));
            }
            if(!$woff->delete()){
                throw new \Exception('Errror:' . json_encode($woff->getErrors()));
            }
        }

        if(!($unloadTransaction->delete())){
            throw new \Exception('Errror:' . json_encode($unloadTransaction->getErrors()));
        }

        return $loadTranIdList;

    }

    /**
     * @param int $refId
     * @param int $refRecordId
     * @return StoreTransactions[]
     */
    public static  function getLoadTran(int $refId, int $refRecordId): array
    {
        return StoreTransactions::findAll([
            'action' => StoreTransactions::ACTION_LOAD,
            'ref_id' => $refId,
            'ref_record_id' => $refRecordId
        ]);
    }

    /**
     * @param int $refId
     * @param int $refRecordId
     * @return StoreTransactions[]
     */
    public static  function getUnLoadTran(int $refId, int $refRecordId): array
    {
        return StoreTransactions::find()
            ->select([
                'ut.*'
            ])
            ->innerJoin('store_woff', 'store_transactions.id = store_woff.load_tran_id')
            ->innerJoin('store_transactions AS ut', 'store_woff.unload_tran_id = ut.id')
            ->andWhere
            ([
                'store_transactions.action' => StoreTransactions::ACTION_LOAD,
                'store_transactions.ref_id' => $refId,
                'store_transactions.ref_record_id' => $refRecordId
            ])
            ->all();
    }


}