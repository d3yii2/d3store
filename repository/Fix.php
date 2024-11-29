<?php


namespace d3yii2\d3store\repository;


use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3store\models\StoreFixes;
use d3yii2\d3store\models\StoreTransactions;
use Yii;
use yii\base\UserException;
use yii\db\ActiveRecord;
use yii\db\Exception;

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
     * @param float $qnt - new quantity
     * @param null|int $userId
     * @param null|ActiveRecord|object $refModel
     * @throws D3ActiveRecordException
     * @throws UserException|Exception
     */
    public function register(float $qnt, int $userId = null, $refModel = null): void
    {
        if ($qnt < 0) {
            throw new UserException(Yii::t('d3store','New fix quantity cannot be negative'));
        }
        $fixQnt = $qnt - $this->transaction->remain_quantity;
        $this->transaction->remain_quantity = $qnt;
        $fixModel = new StoreFixes();
        $fixModel->transaction_id = $this->transaction->id;
        $fixModel->user_id = $userId;
        $fixModel->quantity = $fixQnt;
        if ($refModel) {
            $fixModel->refModelObject = $refModel;
            $fixModel->ref_model_record_id = $refModel->primaryKey;
        }
        if (!$fixModel->save()) {
            throw new D3ActiveRecordException($fixModel);
        }

        if (!$this->transaction->save()) {
            throw new D3ActiveRecordException($this->transaction);
        }
    }

}