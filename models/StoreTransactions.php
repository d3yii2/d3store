<?php

namespace d3yii2\d3store\models;

use \d3yii2\d3store\models\base\StoreTransactions as BaseStoreTransactions;
use d3yii2\d3store\repository\Transactions;

/**
 * This is the model class for table "store_transactions".
 */
class StoreTransactions extends BaseStoreTransactions
{

    /**
     * @inheritdoc
     */
    public function rules()
    {

        return array_merge(
            parent::rules(),
            [
                ['quantity', 'validateQuantity'],
            ]);
    }

    public function validateQuantity($attribute)
    {
        if (self::ACTION_MOVE === $this->action || self::ACTION_UNLOAD === $this->action) {
            $balance = Transactions::getStackBalance($this->stack_from);
            if ($balance < $this->$attribute) {
                $error = \Yii::t('d3store', 'Stacks actual balance is smallest requested quantity');
                $this->addError($attribute, $error);
            }
        }
    }

    public function createMove()
    {
        return Transactions::moveFifo(new \DateTime($this->tran_time), $this->quantity, $this->stack_from, $this->stack_to);
    }

}
