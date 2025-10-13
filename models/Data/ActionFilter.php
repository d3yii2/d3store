<?php

namespace d3yii2\d3store\models\Data;

use yii\base\Component;

class ActionFilter extends Component
{
    public ?string $label = null;
    public ?string $code = null;
    public ?string $description = null;
    public ?array $filterStackTo = null;
    public ?array $filterStackFrom = null;
    public ?array $filterBaseAction = null;
}