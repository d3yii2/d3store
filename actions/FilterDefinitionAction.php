<?php

namespace d3yii2\d3store\actions;

use yii\base\Action;

/**
 * Class FilterDefinitionAction
 * @package d3yii2\d3store\actions
 */
class FilterDefinitionAction extends Action
{

    public array $filterConfig = [];

    public function run(): string
    {
        return $this->controller->render(

            '@vendor/d3yii2/d3store/views/filter-definition/filter-definition',
            [
                'config' => $this->filterConfig,
            ]

        );
    }

}
