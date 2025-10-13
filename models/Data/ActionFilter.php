<?php

namespace d3yii2\d3store\models\Data;

use d3yii2\d3store\dictionaries\StackDictionary;
use yii\base\Component;

class ActionFilter extends Component
{
    private static array $dictionary = [];
    public ?string $label = null;
    public ?string $code = null;
    public ?string $description = null;
    public ?array $filterStackTo = null;
    public ?array $filterStackFrom = null;
    public ?array $filterBaseAction = null;

    public function filterStackToList(): array
    {
        return $this->createList($this->filterStackTo);
    }
    public function filterStackFromList(): array
    {
        return $this->createList($this->filterStackFrom);
    }

    /**
     * @param array|null $stacks
     * @return array
     */
    private function createList(?array $stacks): array
    {
        if (!$stacks) {
            return [];
        }
        $list = [];
        if (!self::$dictionary) {
            self::$dictionary = StackDictionary::getList();
        }
        foreach ($stacks as $stackTo) {
            $list[$stackTo] = self::$dictionary[$stackTo] ?? $stackTo;
        }
        return $list;
    }
}