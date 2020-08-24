<?php


namespace d3yii2\d3store\repository;


use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3store\models\StoreFixes;
use d3yii2\d3store\models\StoreTransactions;
use yii\db\ActiveRecord;

class Fix
{

    /** @var StoreTransactions */
    private $transaction;

    /**
     * Fix constructor.
     * @param StoreTransactions $transaction
     */
    public function __construct(StoreTransactions $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * @param float $qnt
     * @param null|int $userId
     * @param null|ActiveRecord|object $refModel
     * @throws D3ActiveRecordException
     */
    public function register(float $qnt, $userId = null, $refModel = null): void
    {
        $this->transaction->remain_quantity += $qnt;
        $fixModel = new StoreFixes();
        $fixModel->transaction_id = $this->transaction->id;
        $fixModel->user_id = $userId;
        $fixModel->quantity = $qnt;
        if ($refModel) {
            $fixModel->refModelObject = $refModel;
            $primaryKeyField = $refModel->primaryKey;
            $fixModel->ref_model_record_id = $refModel->$$primaryKeyField;
        }
        if (!$fixModel->save()) {
            throw new D3ActiveRecordException($fixModel);
        }

        if (!$this->transaction->save()) {
            throw new D3ActiveRecordException($this->transaction);
        }
    }

}