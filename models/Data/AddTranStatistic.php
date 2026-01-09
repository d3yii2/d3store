<?php

namespace d3yii2\d3store\models\Data;

use DateTime;
use Yii;
use yii\base\Model;
use yii\db\Query;

class AddTranStatistic extends Model
{

    /**
     * attributes
     */
    public ?string $addTypeName = null;
    public ?string $userName = null;
    public ?int $cnt = null;

    public function rules(): array
    {
        return [
            [['addTypeName', 'userName'], 'string'],
            [['cnt'], 'integer'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'addTypeName' => Yii::t('d3store', 'Action'),
            'cnt' => Yii::t('d3store', 'Count'),
            'userName' => Yii::t('d3store', 'User'),
        ];
    }

    public static function findForDay(DateTime $date, bool $addUsers = false): array
    {
        $dateFrom = (clone $date)->setTime(0, 0);
        $dateTo = (clone $date)->setTime(23, 59, 59);
        return self::find($dateFrom, $dateTo, $addUsers);
    }

    /**
     * @param DateTime $dateFrom
     * @param DateTime $dateTo
     * @param bool $addUsers
     * @return AddTranStatistic[]
     */
    private static function find(DateTime $dateFrom, DateTime $dateTo, bool $addUsers = false): array
    {
        $subQuery = (new Query())
            ->select([
                'type_id' => 'IFNULL(tAdd.type_id, 0)',
                'cnt' => 'COUNT(*)'
            ])
            ->from(['t' => 'store_transactions'])
            ->leftJoin(
                ['tAdd' => 'store_tran_add'],
                't.id = tAdd.transactions_id'
            )
            ->where(['between', 't.tran_time', $dateFrom->format('Y-m-d H:i:s'), $dateTo->format('Y-m-d H:i:s')])
            ->groupBy('tAdd.type_id');
        if ($addUsers) {
            $subQuery
                ->addSelect([
                    'user_id' => 'tAdd.user_id'
                ])
                ->addGroupBy('tAdd.user_id');
        }

        $query = (new Query())
            ->select([
                'addTypeName' => "CASE `t`.`type_id` WHEN 0 THEN '" . Yii::t(
                        'd3store',
                        'Undefined'
                    ) . "' ELSE `addType`.`name` END",
                'cnt' => 't.cnt'
            ])
            ->from(['t' => $subQuery])
            ->leftJoin(
                ['addType' => 'store_tran_add_type'],
                't.type_id = addType.id'
            )
            ->orderBy(['addTypeName' => SORT_ASC]);
        if ($addUsers) {
            $query->addSelect([
                'userName' => 'IFNULL(user.username, "' . Yii::t('d3store', 'Unknown') . '")'
            ])
                ->leftJoin(
                    'user',
                    'user.id = t.user_id'
                );
        }
        $models = [];
        foreach ($query->all() as $row) {
            $models[] = new self($row);
        }
        return $models;
    }
}
