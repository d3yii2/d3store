<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace d3yii2\d3store\models\base;

use Yii;

/**
 * This is the base-model class for table "store_stack".
 *
 * @property integer $id
 * @property integer $store_id
 * @property string $name
 * @property string $type
 * @property string $product_name
 * @property integer $capacity
 * @property string $notes
 * @property integer $active
 *
 * @property \d3yii2\d3store\models\StoreStore $store
 * @property \d3yii2\d3store\models\StoreTransactions[] $storeTransactions
 * @property \d3yii2\d3store\models\StoreTransactions[] $storeTransactions0
 * @property string $aliasModel
 */
abstract class StoreStack extends \yii\db\ActiveRecord
{



    /**
    * ENUM field values
    */
    const TYPE_STANDARD = 'Standard';
    const TYPE_TEHNICAL = 'Tehnical';
    var $enum_labels = false;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'store_stack';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id'], 'required'],
            [['store_id', 'capacity', 'active'], 'integer'],
            [['type', 'notes'], 'string'],
            [['name'], 'string', 'max' => 50],
            [['product_name'], 'string', 'max' => 255],
            [['store_id'], 'exist', 'skipOnError' => true, 'targetClass' => \d3yii2\d3store\models\StoreStore::className(), 'targetAttribute' => ['store_id' => 'id']],
            ['type', 'in', 'range' => [
                    self::TYPE_STANDARD,
                    self::TYPE_TEHNICAL,
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('d3store', 'ID'),
            'store_id' => Yii::t('d3store', 'Store'),
            'name' => Yii::t('d3store', 'Stack name'),
            'type' => Yii::t('d3store', 'Type'),
            'product_name' => Yii::t('d3store', 'Product'),
            'capacity' => Yii::t('d3store', 'Capacity'),
            'notes' => Yii::t('d3store', 'Notes'),
            'active' => Yii::t('d3store', 'Active'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStore()
    {
        return $this->hasOne(\d3yii2\d3store\models\StoreStore::className(), ['id' => 'store_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStoreTransactions()
    {
        return $this->hasMany(\d3yii2\d3store\models\StoreTransactions::className(), ['stack_from' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStoreTransactions0()
    {
        return $this->hasMany(\d3yii2\d3store\models\StoreTransactions::className(), ['stack_to' => 'id']);
    }


    
    /**
     * @inheritdoc
     * @return \coalmar\delivery\models\StoreStackQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \coalmar\delivery\models\StoreStackQuery(get_called_class());
    }


    /**
     * get column type enum value label
     * @param string $value
     * @return string
     */
    public static function getTypeValueLabel($value){
        $labels = self::optsType();
        if(isset($labels[$value])){
            return $labels[$value];
        }
        return $value;
    }

    /**
     * column type ENUM value labels
     * @return array
     */
    public static function optsType()
    {
        return [
            self::TYPE_STANDARD => Yii::t('d3store', self::TYPE_STANDARD),
            self::TYPE_TEHNICAL => Yii::t('d3store', self::TYPE_TEHNICAL),
        ];
    }

}
