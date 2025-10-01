<?php

namespace d3yii2\d3store\models;

use cewood\cwatlikumi\AtlikumiConfig;
use cewood\cwmazapecapstrade\CwMpConfig;
use ea\app\config\SysConfig;
use yii\db\ActiveQuery;
use yii\db\Expression;

class StoreTransactionsSearch extends StoreTransactions
{
    public ?string $storeIds = null;

    public function applySearch(ActiveQuery $query): ActiveQuery
    {
        $query
            ->leftJoin(
                'store_stack store_stack_from',
                'store_stack_from.id = store_transactions.stack_from'
            )
            ->leftJoin(
                'store_stack store_stack_to',
                'store_stack_to.id = store_transactions.stack_to'
            )
            ->andWhere([
                'store_transactions.ref_id' => AtlikumiConfig::STORE_REF_PACK_ID,
            ])
            ->addSelect([
                'storeIds' => 'CONCAT(IFNULL(store_stack_from.store_id,0),"/",IFNULL(store_stack_to.store_id,0))',
                'action' => new Expression(
                    "CASE store_stack_to.id
                            WHEN :readyId THEN :customAction
                            ELSE store_transactions.action
                        END",
                    [
                        ':readyId' => SysConfig::STORE_SORTING_STACK_READY,
                        ':customAction' => self::ACTION_EXTENDED_TO_SUPPLEMENT,
                    ]
                ),
            ]);

        if($this->isExtendedAction()) {
            if ($this->action === self::ACTION_EXTENDED_TO_SUPPLEMENT) {
                $query->andWhere(
                    [
                        'or',
                        ['store_stack_to.store_id' => SysConfig::STORE_SORTING],
                        ['store_stack_to.id' => SysConfig::STORE_SORTING_STACK_READY],
                    ],
                );
            }
            if ($this->action === self::ACTION_EXTENDED_SUPPLEMENTED) {
                $query->andWhere(
                    [
                        'or',
                        ['store_stack_from.store_id' => SysConfig::STORE_SORTING],
                        ['store_stack_to.id' => SysConfig::STORE_SORTING_STACK_READY],
                    ],
                );
            }
            if ($this->action === self::ACTION_EXTENDED_READY) {
                $query->andWhere(
                    [
                        'or',
                        ['store_stack_from.store_id' => SysConfig::STORE_SORTING],
                        ['store_stack_to.id' => CwMpConfig::getGatavsStackId()],
                    ],
                );
            }
            if ($this->action === self::ACTION_EXTENDED_REPROCESSED) {
                $query->andWhere(
                    [
                        'or',
                        ['store_stack_to.id' => SysConfig::STORE_SORTING_STACK_OPERATION],
                        ['store_stack_to.id' => SysConfig::STORE_SORTING_STACK_OPERATION2],
                        ['store_stack_to.id' => CwMpConfig::getProcessStackList()],
                    ],
                );
            }
            if ($this->action === self::ACTION_EXTENDED_RETURNED) {
                $query->andWhere(
                    [
                        'or',
                        ['store_stack_to.id' => SysConfig::STORE_SORTING_STACK_OPERATION],
                        ['store_stack_to.id' => SysConfig::STORE_SORTING_STACK_OPERATION2],
                        ['store_stack_to.id' => CwMpConfig::getProcessStackList()],
                    ],
                );
            }
            if ($this->action === self::ACTION_EXTENDED_CORRECTIONS) {
                $query->innerJoin(
                    'store_fixes',
                    'store_fixes.transaction_id = store_transactions.id'
                );
            }
        }

        if ($this->storeIds) {
            $query->andWhere(
                'store_stack_from.store_id = :storeId OR store_stack_to.store_id = :storeId',
                ['storeId' => $this->storeIds]
            );
        }

        return $query;
    }

    public function isExtendedAction(): bool
    {
        return in_array($this->action, self::EXTENDED_ACTIONS, true);
    }
}
