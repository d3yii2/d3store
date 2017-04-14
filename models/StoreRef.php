<?php

namespace d3yii2\d3store\models;

use Yii;
use \d3yii2\d3store\models\base\StoreRef as BaseStoreRef;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "store_ref".
 */
class StoreRef extends BaseStoreRef
{

public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                # custom behaviors
            ]
        );
    }

    public function rules()
    {
        return ArrayHelper::merge(
             parent::rules(),
             [
                  # custom validation rules
             ]
        );
    }
}
