<?php

namespace d3yii2\d3store\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use eaBlankonThema\widget\ThRmGridView;

/**
* StoreStackSearch represents the model behind the search form about `cewood\cwstore\models\StoreStack`.
*/
class StoreStackSearch extends StoreStack
{

    /**
    * @inheritdoc
    */
    public function rules(): array
{
    return [
        [['id', 'store_id', 'capacity', 'active'], 'integer'],
        [['name', 'type', 'product_name', 'notes'], 'safe'],
    ];
    }

    /**
    * @inheritdoc
    */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = self::find();


        
        $this->load(ThRmGridView::getMergedFilterStateParams());

        if (!$this->validate()) {
            return new ActiveDataProvider([
                'query' => $query,

            ]);
        }


        $query
            ->andFilterWhere([
                'store_stack.id' => $this->id,
                'store_stack.store_id' => $this->store_id,
                'store_stack.capacity' => $this->capacity,
                'store_stack.active' => $this->active,
            ])
            ->andFilterWhere(['like', 'store_stack.name', $this->name])
            ->andFilterWhere(['like', 'store_stack.type', $this->type])
            ->andFilterWhere(['like', 'store_stack.product_name', $this->product_name])
            ->andFilterWhere(['like', 'store_stack.notes', $this->notes])
;
        return new ActiveDataProvider([
            'query' => $query,
            //'sort' => ['defaultOrder' => ['????' => SORT_ASC]]
            'pagination' => [
                'params' => ThRmGridView::getMergedFilterStateParams(),
            ],
            'sort' => [
                'params' => ThRmGridView::getMergedFilterStateParams(),
            ],
        ]);
    }
}