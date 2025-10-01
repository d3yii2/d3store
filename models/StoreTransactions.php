<?php

namespace d3yii2\d3store\models;

use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3store\models\base\StoreTransactions as BaseStoreTransactions;
use d3yii2\d3store\repository\Transactions;
use DateTime;
use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "store_transactions".
 * @method static self|null findOne($condition)
 * @method static self[] findAll($condition)
 */
class StoreTransactions extends BaseStoreTransactions
{
    public const ACTION_EXTENDED_TO_SUPPLEMENT = 'to_supplement';
    public const ACTION_EXTENDED_SUPPLEMENTED = 'supplemented';
    public const ACTION_EXTENDED_READY = 'ready';
    public const ACTION_EXTENDED_REPROCESSED = 'reprocessed';
    public const ACTION_EXTENDED_RETURNED = 'returned';
    public const ACTION_EXTENDED_CORRECTIONS = 'corrections';

    public const EXTENDED_ACTIONS = [
        self::ACTION_EXTENDED_TO_SUPPLEMENT,
        self::ACTION_EXTENDED_SUPPLEMENTED,
        self::ACTION_EXTENDED_READY,
        self::ACTION_EXTENDED_REPROCESSED,
        self::ACTION_EXTENDED_RETURNED,
        self::ACTION_EXTENDED_CORRECTIONS,
    ];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                ['quantity', 'validateQuantity'],
            ]
        );
    }

    public function validateQuantity($attribute): void
    {
        if ($this->isNewRecord) {
            if (self::ACTION_MOVE === $this->action || self::ACTION_UNLOAD === $this->action) {
                $balance = Transactions::getStackBalance($this->stack_from);
                if (round($balance, 3) < round($this->$attribute, 3)) {
                    $error = Yii::t(
                        'd3store',
                        'Stacks actual balance {stockBalance} is smallest requested {quantity} quantity',
                        [
                            'stockBalance' => round($balance, 3),
                            'quantity' => round($this->$attribute, 3),
                        ]
                    );
                    $this->addError($attribute, $error);
                }
            }
        }
    }

    public static function optsExtendedAction(): array
    {
        return [
            self::ACTION_LOAD => 'Reģistrēts',
            self::ACTION_MOVE => 'Pārvietots',
            self::ACTION_UNLOAD => 'Norakstīts',
            self::ACTION_EXTENDED_TO_SUPPLEMENT => 'Uz papildināšanu',
            self::ACTION_EXTENDED_SUPPLEMENTED => "Papildināts",
            self::ACTION_EXTENDED_READY => 'Gatavs',
            self::ACTION_EXTENDED_REPROCESSED => 'Pārstrādāts',
            self::ACTION_EXTENDED_RETURNED => 'Atgriezts',
            self::ACTION_EXTENDED_CORRECTIONS => 'Korekcijas',
        ];
}


    /**
     * @throws \yii\base\Exception
     * @throws \Exception
     */
    public function createMove(): bool
    {
        return (bool)Transactions::moveFifo(new DateTime($this->tran_time), $this->quantity, $this->stack_from, $this->stack_to);
    }

    /**
     * save alternative quantity
     * @throws Exception
     * @throws D3ActiveRecordException
     */
    public function createTranAdd(int $typeId, float $qnt): StoreTranAdd
    {
        $tranAdd = new StoreTranAdd([
            'type_id' => $typeId,
            'transactions_id' => $this->id,
            'quantity' => $qnt,
            'remain_quantity' => $qnt,
        ]);
        if (!$tranAdd->save()) {
            throw new D3ActiveRecordException($tranAdd);
        }
        return $tranAdd;
    }
}
