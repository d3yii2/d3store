<?php

namespace d3yii2\d3store\models;

use Yii;
use \d3yii2\d3store\models\base\StoreWoff as BaseStoreWoff;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "store_woff".
 */
class StoreWoff extends BaseStoreWoff
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
