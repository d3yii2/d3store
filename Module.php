<?php

namespace d3yii2\d3store;

class Module extends \yii\base\Module
{

    public $controllerNamespace = 'd3modules\d3ldz\controllers';

    public function getLabel(){
        return \Yii::t('d3store', 'D3Store');
    }


}