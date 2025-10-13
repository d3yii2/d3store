<?php

namespace d3yii2\d3store\models;

use cewood\cwatlikumi\models\StoreTransactions;
use d3system\behaviors\D3DateTimeBehavior;
use d3yii2\d3store\models\Data\ActionFilter;
use RuntimeException;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;


/**
 * StoreTransactionsSearch represents the model behind the search form about `cewood\cwatlikumi\models\StoreTransactions`.
 *
 */
class StoreTransactionsSearch extends StoreTransactions
{
    public function behaviors(): array
    {
        return D3DateTimeBehavior::getConfig(['tran_time']);
    }

    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                ['tran_time_local', 'safe']
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function scenarios(): array
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }


    /**
     * @param int $refId
     * @return ActiveQuery
     */
    public function createQuery(int $refId): ActiveQuery
    {
        return self::find()
            ->select([
                'id' => 'store_transactions.id',
                'action' => 'store_transactions.action',
                'tran_time' => 'store_transactions.tran_time',
                'stack_from' => 'store_transactions.stack_from',
                'stack_to' => 'store_transactions.stack_to',
                'quantity' => 'store_transactions.quantity',
                'remain_quantity' => 'store_transactions.remain_quantity',
                'ref_id' => 'store_transactions.ref_id',
                'ref_record_id' => 'store_transactions.ref_record_id',
                'add_ref_id' => 'store_transactions.add_ref_id',
                'add_ref_record_id' => 'store_transactions.add_ref_record_id',
            ])
            ->andWhere([
                'store_transactions.ref_id' => $refId
            ])
            ->andFilterWhere([
                'store_transactions.id' => $this->id,
                'store_transactions.stack_from' => $this->stack_from,
                'store_transactions.stack_to' => $this->stack_to,
                'store_transactions.quantity' => $this->quantity,
                'store_transactions.remain_quantity' => $this->remain_quantity,
                'store_transactions.ref_id' => $this->ref_id,
                'store_transactions.ref_record_id' => $this->ref_record_id,
                'store_transactions.add_ref_id' => $this->add_ref_id,
                'store_transactions.add_ref_record_id' => $this->add_ref_record_id,
            ])
            ->andFilterWhereDateRange('store_transactions.tran_time', $this->tran_time);
    }

    protected function applyActionFilter(ActiveQuery $query, string $action): ActiveQuery
    {
        foreach (static::getActionMappingConfig() as $filter) {
            if ($filter->code !== $action) {
                continue;
            }
            return $query->andFilterWhere([
                'store_transactions.action' => $filter->filterBaseAction,
                'store_transactions.stack_to' => $filter->filterStackTo,
                'store_transactions.stack_from' => $filter->filterStackFrom
            ]);
        }
        return $query->andWhere(['store_transactions.action' => $action]);
    }

    /**
     * The strict comparison can be risky when comparing against data from a database
     * @noinspection TypeUnsafeArraySearchInspection
     */
    public function createActionLabel(): string
    {
        foreach (static::getActionMappingConfig() as $filter) {
            if ($filter->filterStackTo && !in_array($this->stack_to, $filter->filterStackTo)) {
                continue;
            }

            if ($filter->filterStackFrom && !in_array($this->stack_from, $filter->filterStackFrom)) {
                continue;
            }

            if ($filter->filterBaseAction && !in_array($this->action, $filter->filterBaseAction)) {
                continue;
            }
            return $filter->label;
        }

        if (!$actionLabel = parent::optsAction()[$this->action] ?? null) {
            throw new RuntimeException(
                sprintf('No matching custom action found for action: %s', $this->action)
            );
        }
        return $actionLabel;
    }

    public static function optsAction(): array
    {
        return array_merge(
            parent::optsAction(),
            ArrayHelper::map(static::getActionMappingConfig(), 'code', 'label')
      );
    }
}
