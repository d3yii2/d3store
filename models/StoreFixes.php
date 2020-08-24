<?php

namespace d3yii2\d3store\models;

use d3system\dictionaries\SysModelsDictionary;
use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3store\models\base\StoreFixes as BaseStoreFixes;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "store_fixes".
 */
class StoreFixes extends BaseStoreFixes
{

    /** @var ActiveRecord */
    public $refModelObject;

    /**
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool
     * @throws D3ActiveRecordException
     */
    public function save($runValidation = true, $attributeNames = null): bool
    {
        if ($this->refModelObject) {
            $this->ref_model_id = SysModelsDictionary::getIdByClassName(get_class($this->refModelObject));
        }

        if (!$this->time) {
            $this->time = date('Y-m-d H:i:s');
        }

        return parent::save($runValidation, $attributeNames);
    }
}
