<?php

namespace d3yii2\d3store\repository;

use d3yii2\d3store\models\StoreTransactions;
use d3yii2\d3store\models\StoreWoff;
use yii\base\Exception;
use yii\helpers\VarDumper;


class Transactions
{
    public static $errors = [];

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
            throw new Exception('Error:' . VarDumper::dumpAsString($transaction->errors));
        }

        return $transaction;

    }

    public static function deleteLoad($refId, $refRecordId): bool
    {
        $searchParam = [
            'action' => StoreTransactions::ACTION_LOAD,
            'ref_id' => $refId,
            'ref_record_id' =>  $refRecordId,
        ];
        $tran = StoreTransactions::findOne($searchParam);

        if(!$tran){
            throw new Exception('Can not find transactin. ' . VarDumper::dumpAsString($searchParam));
        }
        if($tran->getStoreWoffs()->one()){
            throw new Exception('Can not delete transactin. Transaction has write off');
        }

        if(!$tran->delete()){
            throw new Exception('Can not delete transactin.' . VarDumper::dumpAsString($tran->getErrors()));
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
        self::clearError();
        $stackBalance = self::getStackBalance($stackFromId, $loadRefId, $loadRefRecordIdList);
        if(round($stackBalance,5) < round($quantity,5)){
            self::registreError('No enough unload quantaty',[
                'stackBalance' => round($stackBalance,5),
                'quantity' => round($quantity,5),
                'stackFromId' => $stackFromId,
                'loadRefId' => $loadRefId,
                'loadRefRecordIdList' => $loadRefRecordIdList
            ]);
            return false;
        }

        return self::writeOff('fifo', $loadRefId, $loadRefRecordIdList, $tranTime, $quantity, $stackFromId, $refId, $refRecordId);

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
        self::clearError();
        $stackBalance = self::getStackBalance($stackFromId, $loadRefId, $loadRefRecordIdList);
        if(round($stackBalance,5) < round($quantity,5)){
            self::registreError('No enough unload quantaty',[
                'stackBalance' => round($stackBalance,5),
                'quantity' => round($quantity,5),
                'stackFromId' => $stackFromId,
                'loadRefId' => $loadRefId,
                'loadRefRecordIdList' => $loadRefRecordIdList
            ]);
            return false;
        }
        return self::writeOff('lifo', $loadRefId, $loadRefRecordIdList, $tranTime, $quantity, $stackFromId, $refId, $refRecordId);
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
            throw new Exception('Error:' . VarDumper::dumpAsString($rT->errors));
        }
        if (!$transaction->save()) {
            throw new Exception('Error:' . VarDumper::dumpAsString($transaction->errors));
        }
        return $transaction;
    }

    /**
     * @param string $type FIFO or ???
     * @param int|bool $loadRefId
     * @param array $loadRefRecordIdList
     * @param \DateTime $tranTime
     * @param float $quantity
     * @param int $stackFromId
     * @param int $refId
     * @param int $refRecordId
     * @return bool|Transactions[]
     * @throws \Exception
     */
    private static function writeOff(
        string $type,
        int $loadRefId,
        array $loadRefRecordIdList,
        \DateTime $tranTime,
        float $quantity,
        int $stackFromId,
        int $refId,
        int $refRecordId
    )
    {

        $sortDirection = ($type === 'fifo' ? SORT_ASC : SORT_DESC);

        $query = StoreTransactions::find()
            ->where(['stack_to' => $stackFromId])
            ->andFilterCompare('remain_quantity', 0, '>')
            ->orderBy(['tran_time' => $sortDirection]);
        if($loadRefId){
            $query->andWhere(['ref_id' => $loadRefId])
                ->andWhere(['ref_record_id' => $loadRefRecordIdList]);
        }

        $remainTran = $query->all();

        $woffQuantity = $quantity;
        $transactionList = [];
        foreach ($remainTran as $rT) {

            if ($woffQuantity > $rT->remain_quantity) {
                $woffQuantity -= $rT->remain_quantity;
                $tranQuantity = $rT->remain_quantity;
                $rT->remain_quantity = 0;
            } else {
                $rT->remain_quantity -= $woffQuantity;
                $tranQuantity = $woffQuantity;
                $woffQuantity = 0;
            }

            $transaction = new StoreTransactions();
            $transaction->action = StoreTransactions::ACTION_UNLOAD;
            $transaction->tran_time = $tranTime->format('Y-m-d H:i:s');
            $transaction->quantity = $tranQuantity;
            $transaction->remain_quantity = 0;
            $transaction->stack_from = $stackFromId;
            $transaction->ref_id = $refId;
            $transaction->ref_record_id = $refRecordId;

            if (!$transaction->save()) {
                throw new Exception('Error:' . VarDumper::dumpAsString($transaction->errors));
            }
            $transactionList[] = $transaction;
            $woff = new StoreWoff();
            $woff->unload_tran_id = $transaction->id;
            $woff->load_tran_id = $rT->id;
            $woff->quantity = $tranQuantity;

            if (!$woff->save()) {
                throw new Exception('Error:' . VarDumper::dumpAsString($woff->errors));
            }
            if (!$rT->save()) {
                throw new Exception('Error:' . VarDumper::dumpAsString($rT->errors));
            }
            if ($woffQuantity < $transaction->quantity/1000000000) {
                break;
            }
        }

        return $transactionList;

    }

    /**
     * @param int $stackId
     * @param int|bool $loadRefId
     * @param array $loadRefRecordIdList
     * @return float
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
                throw new Exception('Errror:' . VarDumper::dumpAsString($loadTran->getErrors()));
            }
            if(!$woff->delete()){
                throw new Exception('Errror:' . VarDumper::dumpAsString($woff->getErrors()));
            }
        }

        if(!($unloadTransaction->delete())){
            throw new \Exception('Errror:' . VarDumper::dumpAsString($unloadTransaction->getErrors()));
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
    public static  function getMoveTran(int $refId, int $refRecordId): array
    {
        return StoreTransactions::findAll([
            'action' => StoreTransactions::ACTION_MOVE,
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

    private static function clearError(){
        self::$errors = [];
    }
    private static function registreError(string $message,$data)
    {
        self::$errors[] = [
            'message' => $message,
            'data' => $data
        ];
    }

    public static function getErrors()
    {
        return self::$errors;
    }

}