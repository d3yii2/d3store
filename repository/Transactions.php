<?php /** @noinspection PhpUndefinedClassInspection */

namespace d3yii2\d3store\repository;

use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3store\models\StoreTransactionFlow;
use d3yii2\d3store\models\StoreTransactions;
use d3yii2\d3store\models\StoreWoff;
use DateTime;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

class Transactions
{
    public static $errors = [];

    /**
     * @param DateTime $tranTime
     * @param float $quantity
     * @param int $stackToId
     * @param int $refId
     * @param int $refRecordId
     * @param int|null $addRefId
     * @param int|null $addRefRecordId
     * @return StoreTransactions
     * @throws Exception
     */
    public static function load(
        DateTime $tranTime,
        float $quantity,
        int $stackToId,
        int $refId,
        int $refRecordId,
        int $addRefId = null,
        int $addRefRecordId = null
    ): StoreTransactions {
        $transaction = new StoreTransactions();
        $transaction->action = StoreTransactions::ACTION_LOAD;
        $transaction->tran_time = $tranTime->format('Y-m-d H:i:s');
        $transaction->quantity = $quantity;
        $transaction->remain_quantity = $quantity;
        $transaction->stack_to = $stackToId;
        $transaction->ref_id = $refId;
        $transaction->ref_record_id = $refRecordId;
        $transaction->add_ref_id = $addRefId;
        $transaction->add_ref_record_id = $addRefRecordId;

        if (!$transaction->save()) {
            throw new Exception('Error:' . VarDumper::dumpAsString($transaction->errors));
        }
        return $transaction;
    }

    /**
     * @param $refId
     * @param $refRecordId
     * @return bool
     * @throws Exception
     * @throws StaleObjectException
     * @throws Throwable
     */
    public static function deleteLoad($refId, $refRecordId): bool
    {
        $searchParam = [
            'action' => StoreTransactions::ACTION_LOAD,
            'ref_id' => $refId,
            'ref_record_id' =>  $refRecordId,
        ];
        $tran = StoreTransactions::findOne($searchParam);

        if (!$tran) {
            throw new Exception('Can not find transactin. ' . VarDumper::dumpAsString($searchParam));
        }
        if ($tran->getStoreWoffs()->one()) {
            throw new Exception('Can not delete transactin. Transaction has write off');
        }
        if (!$tran->delete()) {
            throw new Exception('Can not delete transactin.' . VarDumper::dumpAsString($tran->getErrors()));
        }
        return true;
    }

    /**
     * @param DateTime $tranTime
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
        if (round($stackBalance, 5) < round($quantity, 5)) {
            self::registreError(
                'No enough unload quantity',
                [
                    'stackBalance' => round($stackBalance, 5),
                    'quantity' => round($quantity, 5),
                    'stackFromId' => $stackFromId,
                    'loadRefId' => $loadRefId,
                    'loadRefRecordIdList' => $loadRefRecordIdList
                ]
            );
            return false;
        }
        return self::writeOff('fifo', $loadRefId, $loadRefRecordIdList, $tranTime, $quantity, $stackFromId, $refId, $refRecordId);
    }

    /**
     * @param $tranTime
     * @param float $quantity
     * @param int $stackFromId
     * @param int $refId
     * @param int $refRecordId
     * @param int[] $loadTranIdList
     * @param bool $loadRefId
     * @return StoreTransactions[]
     * @throws Exception
     */
    public static function unLoadFifoByTranId(
        $tranTime,
        $quantity,
        $stackFromId,
        $refId,
        $refRecordId,
        $loadTranIdList,
        $loadRefId = false
    ): array {
        self::clearError();
        return self::writeOffByTran('fifo', $loadRefId, $loadTranIdList, $tranTime, $quantity, $stackFromId, $refId, $refRecordId);
    }

    /**
     * @param DateTime $tranTime
     * @param float $quantity
     * @param int $stackFromId
     * @param int $refId
     * @param int $refRecordId
     * @param bool $loadRefId
     * @param int[] $loadRefRecordIdList
     * @return bool|Transactions[]
     * @throws \Exception
     */
    public static function unLoadLifo($tranTime, $quantity, $stackFromId, $refId, $refRecordId, $loadRefId = false, $loadRefRecordIdList = [])
    {
        self::clearError();
        $stackBalance = self::getStackBalance($stackFromId, $loadRefId, $loadRefRecordIdList);
        if (round($stackBalance, 5) < round($quantity, 5)) {
            self::registreError(
                'No enough unload quantaty',
                [
                    'stackBalance' => round($stackBalance, 5),
                    'quantity' => round($quantity, 5),
                    'stackFromId' => $stackFromId,
                    'loadRefId' => $loadRefId,
                    'loadRefRecordIdList' => $loadRefRecordIdList
                ]
            );
            return false;
        }
        return self::writeOff('lifo', $loadRefId, $loadRefRecordIdList, $tranTime, $quantity, $stackFromId, $refId, $refRecordId);
    }

    /**
     * @param DateTime $tranTime
     * @param float $quantity
     * @param int $stackFromId
     * @param int $stackToId
     * @param int $addRefId
     * @param int $addRefRecordId
     * @param int $loadRefId
     * @param array $loadRefRecordIdList
     * @return StoreTransactions[]
     * @throws Exception
     */
    public static function moveFifo(
        DateTime $tranTime,
        float $quantity,
        int $stackFromId,
        int $stackToId,
        int $addRefId = 0,
        int $addRefRecordId = 0,
        int $loadRefId = 0,
        array $loadRefRecordIdList = []
    ): array {
        self::clearError();

        /**
         * checking or getting enough
         */
        $stackBalance = self::getStackBalance($stackFromId, $loadRefId, $loadRefRecordIdList);
        if (round($stackBalance, 5) < round($quantity, 5)) {
            self::registreError('No enough quantity for transfer', [
                'stackBalance' => round($stackBalance, 5),
                'transfer quantity' => round($quantity, 5),
                'stackFromId' => $stackFromId,
                'loadRefId' => $loadRefId,
                'loadRefRecordIdList' => $loadRefRecordIdList
            ]);
            return [];
        }

        /**
         * get remain transactions sorted by transaction time
         */
        $query = StoreTransactions::find()
            ->where(['stack_to' => $stackFromId])
            ->andFilterCompare('remain_quantity', 0, '>')
            ->orderBy(['tran_time' => SORT_DESC]);
        if ($loadRefId) {
            $query->andWhere(['ref_id' => $loadRefId])
                ->andWhere(['ref_record_id' => $loadRefRecordIdList]);
        }
        $remainTran = $query->all();

        /**
         * extra sorting for more one level moving
         */
        $SortedRemainTran = [];
        foreach ($remainTran as $k =>$tran) {
            $prevTran = clone $tran;
            while ($prevTran->action !== StoreTransactions::ACTION_LOAD) {
                /** @var StoreTransactionFlow $flow */
                if (!$flow = $prevTran->getStoreTransactionFlowsNext()->one()) {
                    throw new Exception('Ca not found flow for StoreTransactions: ' . VarDumper::dumpAsString($prevTran->attributes()));
                }
                if(!$prevTran = $flow->getPrevTran()->one()){
                    throw new Exception('Ca not found prev transaction for StoreTransactionFlow=' . VarDumper::dumpAsString($flow->attributes()));
                }
            }

            $key = $prevTran->tran_time . $tran->id;
            $SortedRemainTran[$key] = $tran;
            unset($remainTran[$k]);
        }
        ksort($SortedRemainTran);

        /**
         * registering move transactions and reduce common move quantity while is not empty
         */
        $moveQuantity = $quantity;
        $moveTransactionList = [];
        foreach ($SortedRemainTran as $rT) {
            if ($moveQuantity > $rT->remain_quantity) {
                $moveQuantity -= $rT->remain_quantity;
                $tranQuantity = $rT->remain_quantity;
            } else {
                $tranQuantity = $moveQuantity;
                $moveQuantity = 0;
            }
            $moveTransactionList[] = self::moveTransaction($tranTime, $stackToId, $rT, $tranQuantity,$addRefId,$addRefRecordId);

            if ($moveQuantity < $quantity/1000000000) {
                break;
            }
        }
        return $moveTransactionList;

    }


    /**
     * @param DateTime $tranTime
     * @param float $quantity
     * @param int $stackFromId
     * @param int $stackToId
     * @param int $addRefId
     * @param int $addRefRecordId
     * @param int $loadRefId
     * @param array $tranIdList
     * @return array
     * @throws Exception
     */
    public static function transactionsMoveFifo(
        DateTime $tranTime,
        float $quantity,
        int $stackFromId,
        int $stackToId,
        int $loadRefId,
        array $tranIdList,
        int $addRefId = 0,
        int $addRefRecordId = 0
    ): array
    {
        self::clearError();



        /**
         * get remain transactions sorted by transaction time
         */
        $query = StoreTransactions::find()
            ->where([
                'stack_to' => $stackFromId,
                'id' => $tranIdList,
                'ref_id' => $loadRefId
            ])
            ->andFilterCompare('remain_quantity', 0, '>')
            ->orderBy(['tran_time' => SORT_DESC]);

        $remainTran = $query->all();

        /**
         * extra sorting for more one level moving
         */
        $SortedRemainTran = [];
        foreach ($remainTran as $k =>$tran){

            $prevTran = clone $tran;
            while($prevTran->action !== StoreTransactions::ACTION_LOAD){
                /** @var StoreTransactionFlow $flow */
                if(!$flow = $prevTran->getStoreTransactionFlowsNext()->one()){
                    throw new Exception('Ca not found flow for StoreTransactions: ' . VarDumper::dumpAsString($prevTran->attributes()));
                }
                if(!$prevTran = $flow->getPrevTran()->one()){
                    throw new Exception('Ca not found prev transaction for StoreTransactionFlow=' . VarDumper::dumpAsString($flow->attributes()));
                }
            }

            $key = $prevTran->tran_time . $tran->id;
            $SortedRemainTran[$key] = $tran;
            unset($remainTran[$k]);
        }

        ksort($SortedRemainTran);

        /**
         * registering move transactions and reduce common move quantity while is not empty
         */
        $moveQuantity = $quantity;
        $moveTransactionList = [];
        foreach ($SortedRemainTran as $rT) {

            if ($moveQuantity > $rT->remain_quantity) {
                $moveQuantity -= $rT->remain_quantity;
                $tranQuantity = $rT->remain_quantity;
            } else {
                $tranQuantity = $moveQuantity;
                $moveQuantity = 0;
            }

            $moveTransactionList[] = self::moveTransaction($tranTime, $stackToId, $rT, $tranQuantity,$addRefId,$addRefRecordId);

            if ($moveQuantity < $quantity/1000000000) {
                break;
            }
        }
        return $moveTransactionList;

    }

    /**
     * @param int $reId
     * @param int $refRecordId
     * @throws Exception
     * @throws StaleObjectException
     * @throws Throwable
     */
    public static function deleteMoveTransaction(int $reId, int $refRecordId): void
    {
        foreach(StoreTransactions::find()
                    ->where(['add_ref_id'=>$reId,'add_ref_record_id' => $refRecordId])
                    ->orderBy(['id' => SORT_DESC])
                    ->all()
                as $moveTran
        ){
            self::deleteMoveTransactionByTran($moveTran);
        }
    }


    /**
     * create store transaction, reducing remain in prev transaction and create move transaction
     *
     * @param DateTime $tranTime transaction time
     * @param int $stackToId move to stack
     * @param StoreTransactions $rT source transaction
     * @param float $tranQuantity transaction quantity
     * @param int $addRefId additional ref object
     * @param int $addRefRecordId additional ref object record id
     * @param int|null $refRecordId alternative ref record id, if required other as in $rT->ref_record_d
     * @return StoreTransactions
     * @throws D3ActiveRecordException
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public static function moveTransaction(
        DateTime $tranTime,
        int $stackToId,
        StoreTransactions $rT,
        float $tranQuantity,
        int $addRefId = 0,
        int $addRefRecordId = 0,
        int $refRecordId = null
    ): StoreTransactions
    {
        if($tranQuantity < 0){
            throw new Exception('Error: quantity must be positive');
        }

        /**
         * move transaction
         */
        $transaction = new StoreTransactions();
        $transaction->action = StoreTransactions::ACTION_MOVE;
        $transaction->tran_time = $tranTime->format('Y-m-d H:i:s');
        $transaction->stack_from = $rT->stack_to;
        $transaction->stack_to = $stackToId;
        $transaction->ref_id = $rT->ref_id;
        $transaction->ref_record_id = $refRecordId??$rT->ref_record_id;
        if($addRefId) {
            $transaction->add_ref_id = $addRefId;
        }
        if($addRefRecordId) {
            $transaction->add_ref_record_id = $addRefRecordId;
        }
        $transaction->quantity = $tranQuantity;
        $transaction->remain_quantity = $tranQuantity;

        if (!$transaction->save()) {
            throw new D3ActiveRecordException($transaction);
        }

        /**
         * reducing preview transaction remain quantity
         */
        $rT->remain_quantity -= $tranQuantity;
        if (!$rT->save()) {
            throw new D3ActiveRecordException($rT);
        }

        /**
         * register flow - prev and next transaction
         */
        $flow = new StoreTransactionFlow();
        $flow->prev_tran_id = $rT->id;
        $flow->next_tran_id = $transaction->id;
        $flow->quantity = $tranQuantity;
        if (!$flow->save()) {
            throw new D3ActiveRecordException($flow);
        }
        return $transaction;
    }

    /**
     * add to store transaction, reducing remain in prev transaction and register  flow
     *
     * @param StoreTransactions $transaction move to this transaction
     * @param StoreTransactions $rT  move from this to $transaction
     * @param ?float $tranQuantity move quantity. if no t defined move all remain quantity
     * @throws Exception
     */
    public static function moveTransactionAdd(
        StoreTransactions $transaction,
        StoreTransactions $rT,
        float $tranQuantity = null
    )
    {
        if ($tranQuantity === null) {
            $tranQuantity = $rT->remain_quantity;
        }
        if($tranQuantity < 0){
            throw new Exception('Error: quantity must be positive');
        }

        $transaction->quantity += $tranQuantity;
        $transaction->remain_quantity += $tranQuantity;

        if (!$transaction->save()) {
            throw new Exception('Error:' . VarDumper::dumpAsString($transaction->errors));
        }

        /**
         * reducing preview transaction remain quantity
         */
        $rT->remain_quantity -= $tranQuantity;
        if (!$rT->save()) {
            throw new Exception('Error:' . VarDumper::dumpAsString($rT->errors));
        }

        /**
         * register flow - prev and next transaction
         */
        $flow = new StoreTransactionFlow();
        $flow->prev_tran_id = $rT->id;
        $flow->next_tran_id = $transaction->id;
        $flow->quantity = $tranQuantity;
        if (!$flow->save()) {
            throw new Exception('Error:' . VarDumper::dumpAsString($flow->errors));
        }
    }

    /**
     * @param string $type FIFO or ???
     * @param int|bool $loadRefId
     * @param array $loadRefRecordIdList
     * @param DateTime $tranTime
     * @param float $quantity
     * @param int $stackFromId
     * @param int $refId
     * @param int $refRecordId
     * @return bool|Transactions[]
     * @throws \Exception
     */
    public static function writeOff(
        string $type,
        int $loadRefId,
        array $loadRefRecordIdList,
        DateTime $tranTime,
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

            $transaction = self::writeOffTran($tranTime, $tranQuantity, $stackFromId, $refId, $refRecordId, $rT);
            if ($woffQuantity < $transaction->quantity/1000000000) {
                break;
            }
        }

        return $transactionList;

    }

    /**
     * @param DateTime $tranTime
     * @param float $tranQuantity
     * @param int $stackFromId
     * @param int $refId
     * @param int $refRecordId
     * @param StoreTransactions $rT
     * @return StoreTransactions
     * @throws Exception
     */
    public static function writeOffTran(
        DateTime $tranTime,
        float $tranQuantity,
        int $stackFromId,
        int $refId,
        int $refRecordId,
        StoreTransactions $rT
    ): StoreTransactions
    {
        $transaction = new StoreTransactions();
        $transaction->action = StoreTransactions::ACTION_UNLOAD;
        $transaction->tran_time = $tranTime->format('Y-m-d H:i:s');
        $transaction->quantity = $tranQuantity;
        $transaction->remain_quantity = 0;
        $transaction->stack_from = $stackFromId;
        $transaction->ref_id = $refId;
        $transaction->ref_record_id = $refRecordId;

        if (!$transaction->save()) {
            throw new Exception('Error:'
                . VarDumper::dumpAsString($transaction->errors) . PHP_EOL
                . 'Transaction: ' .  VarDumper::dumpAsString($rT->attributes) . PHP_EOL
                . 'Woff tran: ' .  VarDumper::dumpAsString($transaction->attributes) . PHP_EOL
                . '$tranQuantity: ' . $tranQuantity . PHP_EOL
                . '$refId: ' . $refId . PHP_EOL
                . '$refRecordId: ' . $refRecordId
            );
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

        return $transaction;
    }


    /**
     * @param string $type
     * @param int $loadRefId
     * @param int[] $loadTranIdList
     * @param DateTime $tranTime
     * @param float $quantity
     * @param int $stackFromId
     * @param int $refId
     * @param int $refRecordId
     * @return array
     * @throws Exception
     */
    private static function writeOffByTran(
        string $type,
        int $loadRefId,
        array $loadTranIdList,
        DateTime $tranTime,
        float $quantity,
        int $stackFromId,
        int $refId,
        int $refRecordId
    ): array
    {

        $sortDirection = ($type === 'fifo' ? SORT_ASC : SORT_DESC);

        $query = StoreTransactions::find()
            ->where([
                'stack_to' => $stackFromId,
                'ref_id' => $loadRefId,
                'id' => $loadTranIdList
            ])
            ->andFilterCompare('remain_quantity', 0, '>')
            ->orderBy(['tran_time' => $sortDirection]);

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
            if ($woffQuantity < 0.000001) {
                break;
            }
        }

        if($woffQuantity > 0.001){
            Yii::error(
                'writeOffByTran: No enough unload $woffQuantity:' . $woffQuantity . PHP_EOL
                . '$loadTranIdList: ' . VarDumper::dumpAsString($loadTranIdList) . PHP_EOL
            );
            throw new Exception('No enough unload quantity:' . $woffQuantity);

        }

        return $transactionList;

    }

    /**
     * @param int $stackId
     * @param int|bool $loadRefId
     * @param array $loadRefRecordIdList
     * @return float
     */
    public static function getStackBalance($stackId, $loadRefId = false, $loadRefRecordIdList = []): float
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
     * @return array
     * @throws \yii\db\Exception
     */
    public static function getAllStacksBalance(): array
    {
        return StoreTransactions::find()
            ->select([
                '`store_stack`.id id',
                'CONCAT(store_store.name, \' - \' ,`store_stack`.`name`) `name`',
                'store_stack.type',
                'store_stack.product_name',
                'store_stack.capacity',
                'store_stack.notes',
                'sum(remain_quantity) remain_quantity'
            ])
            ->innerJoin('`store_stack`', '`store_stack`.id = store_transactions.stack_to')
            ->innerJoin('`store_store`', 'store_store.id = `store_stack`.store_id')
            ->where([
                'store_store.company_id' => Yii::$app->SysCmp->getActiveCompanyId(),
                'store_stack.active' => 1
            ])
            ->groupBy('`store_stack`.id')
            ->asArray()
            ->all();
    }


    /**
     * @param $unLoadTranId
     * @return array
     * @throws Exception
     * @throws Throwable
     * @throws StaleObjectException
     */
    public static function deleteUnload($unLoadTranId): array
    {
        if(!$unloadTransaction = StoreTransactions::findOne($unLoadTranId)){
            throw new Exception('Can not find unload transaction for $unLoadTranId = ' . $unLoadTranId);
        }

        $loadTranIdList = [];
        /** @var StoreWoff $woff */
        foreach(StoreWoff::findAll(['unload_tran_id' => $unLoadTranId]) as $woff){
            /** @var StoreTransactions $loadTran */
            $loadTran = $woff->getLoadTran()->one();
            $loadTranIdList[] = $loadTran->id;
            $loadTran->remain_quantity += (float)$woff->quantity;
            if(!$loadTran->save()){
                throw new Exception('Errror:' . VarDumper::dumpAsString($loadTran->getErrors()));
            }
            if(!$woff->delete()){
                throw new Exception('Errror:' . VarDumper::dumpAsString($woff->getErrors()));
            }
        }

        if(!$unloadTransaction->delete()){
            throw new Exception('Errror:' . VarDumper::dumpAsString($unloadTransaction->getErrors()));
        }

        return $loadTranIdList;
    }

    /**
     * @param int $refId
     * @param int|int[] $refRecordId
     * @return StoreTransactions[]
     */
    public static function getLoadTran(
        int $refId,
        $refRecordId
    ): array {
        return StoreTransactions::findAll([
            'action' => StoreTransactions::ACTION_LOAD,
            'ref_id' => $refId,
            'ref_record_id' => $refRecordId
        ]);
    }

    /**
     * @param int $refId
     * @param int|int[] $refRecordId
     * @return StoreTransactions[]
     */
    public static function getMoveTran(
        int $refId,
        $refRecordId
    ): array {
        return StoreTransactions::findAll([
            'action' => StoreTransactions::ACTION_MOVE,
            'ref_id' => $refId,
            'ref_record_id' => $refRecordId
        ]);
    }

    /**
     * @param int $refId
     * @param int|int[] $refRecordId
     * @return StoreTransactions[]
     */
    public static function getUnLoadTran(
        int $refId,
        $refRecordId
    ): array {
        return StoreTransactions::find()
            ->select([
                'ut.*'
            ])
            ->innerJoin('store_woff', 'store_transactions.id = store_woff.load_tran_id')
            ->innerJoin('store_transactions AS ut', 'store_woff.unload_tran_id = ut.id')
            ->andWhere([
                'store_transactions.action' => [
                    StoreTransactions::ACTION_LOAD,
                    StoreTransactions::ACTION_MOVE,
                ],
                'store_transactions.ref_id' => $refId,
                'store_transactions.ref_record_id' => $refRecordId
            ])
            ->all();
    }

    private static function clearError(): void
    {
        self::$errors = [];
    }
    private static function registreError(string $message,$data): void
    {
        self::$errors[] = [
            'message' => $message,
            'data' => $data
        ];
    }

    public static function getErrors(): array
    {
        return self::$errors;
    }

    /**
     * @param StoreTransactions|null $moveTran
     * @return StoreTransactions
     * @throws Exception
     * @throws StaleObjectException
     * @throws Throwable
     */
    public static function deleteMoveTransactionByTran(StoreTransactions $moveTran): StoreTransactions
    {
        if (!$tranFlowList = StoreTransactionFlow::findAll(['next_tran_id' => $moveTran->id])) {
            throw new Exception('Can not found StoreTransactionFlow for move tran: ' . VarDumper::dumpAsString($moveTran->getAttributes()));
        }
        if (!$prevTran = StoreTransactions::findOne(['id' => ArrayHelper::getColumn($tranFlowList,'prev_tran_id')])) {
            throw new Exception('Can not found Prev Tran for move tran: ' . VarDumper::dumpAsString($moveTran->getAttributes()));
        }
        foreach($tranFlowList as $tranFlow) {
            $prevTran->remain_quantity += (float)$tranFlow->quantity;
        }
        if (!$prevTran->save()) {
            throw new Exception('Can not update prev tran: '
                . VarDumper::dumpAsString($moveTran->getAttributes()
                    . ' Error:' . VarDumper::dumpAsString($prevTran->getErrors())));
        }
        foreach($tranFlowList as $tranFlow) {
            $tranFlow->delete();
        }

        $moveTran->delete();
        return $prevTran;
    }


    /**
     * @param StoreTransactions|null $woffTran
     * @return StoreTransactions[]
     * @throws Exception
     * @throws StaleObjectException
     * @throws Throwable
     */
    public static function deleteWoffTransactionByTran(StoreTransactions $woffTran): array
    {
        if (!$woffList = StoreWoff::findAll(['unload_tran_id' => $woffTran->id])) {
            throw new Exception('Can not found StoreWoff for move tran: ' . VarDumper::dumpAsString($woffTran->getAttributes()));
        }

        $loadTranList = [];
        foreach($woffList as $woff) {
            if (!$loadTran = StoreTransactions::findOne($woff->load_tran_id)) {
                throw new Exception('Can not found Load Tran for woff tran: ' . PHP_EOL
                    . '$woffTran: ' . VarDumper::dumpAsString($woffTran->getAttributes()) . PHP_EOL
                    . '$woff: ' .  VarDumper::dumpAsString($woff->getAttributes())
                );
            }
            $loadTran->remain_quantity += (float)$woff->quantity;
            if (!$loadTran->save()) {
                throw new Exception('Can not update prev tran: '
                    . VarDumper::dumpAsString($woffTran->getAttributes()
                        . ' Error:' . VarDumper::dumpAsString($loadTran->getErrors())));
            }
            $loadTranList[] = $loadTran;
            $woff->delete();
        }

        $woffTran->delete();
        return $loadTranList;
    }

}