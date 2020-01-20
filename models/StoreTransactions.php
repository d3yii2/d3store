<?php

namespace d3yii2\d3store\models;

use \d3yii2\d3store\models\base\StoreTransactions as BaseStoreTransactions;
use d3yii2\d3store\repository\Transactions;
use DateTime;
use Yii;

/**
 * This is the model class for table "store_transactions".
 * @method static self|null findOne($condition)
 * @method static self[] findAll($condition)
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

    public function validateQuantity($attribute): void
    {
        if($this->isNewRecord) {
            if (self::ACTION_MOVE === $this->action || self::ACTION_UNLOAD === $this->action) {
                $balance = Transactions::getStackBalance($this->stack_from);
                if (round($balance,3) < round($this->$attribute,3)) {
                    $error = Yii::t('d3store', 'Stacks actual balance is smallest requested quantity');
                    $this->addError($attribute, $error);
                }
            }
        }
    }

    public function createMove(): bool
    {
        return (bool)Transactions::moveFifo(new DateTime($this->tran_time), $this->quantity, $this->stack_from, $this->stack_to);
    }

}
