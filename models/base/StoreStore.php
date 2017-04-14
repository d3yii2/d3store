<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace d3yii2\d3store\models\base;

use Yii;

/**
 * This is the base-model class for table "store_store".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $name
 * @property string $address
 * @property integer $capacity
 * @property integer $active
 *
 * @property \d3yii2\d3store\models\StoreStack[] $storeStacks
 * @property string $aliasModel
 */
abstract class StoreStore extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'store_store';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id'], 'required'],
            [['company_id', 'capacity', 'active'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['address'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('d3store', 'ID'),
            'company_id' => Yii::t('d3store', 'Company ID'),
            'name' => Yii::t('d3store', 'Store Name'),
            'address' => Yii::t('d3store', 'Store Address'),
            'capacity' => Yii::t('d3store', 'Capacity'),
            'active' => Yii::t('d3store', 'Active'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStoreStacks()
    {
        return $this->hasMany(\d3yii2\d3store\models\StoreStack::className(), ['store_id' => 'id']);
    }


    
    /**
     * @inheritdoc
     * @return \coalmar\delivery\models\StoreStoreQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \coalmar\delivery\models\StoreStoreQuery(get_called_class());
    }


}
