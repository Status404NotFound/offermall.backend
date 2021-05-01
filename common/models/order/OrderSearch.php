<?php

namespace common\models\order;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * OrderSearch represents the model behind the search form about `common\models\order\Order`.
 */
class OrderSearch extends OrderView
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'offer_id', 'owner_id', 'customer_id', 'order_status', 'total_count', 'order_hash'], 'integer'],
            [['delivery_date', 'created_at', 'updated_at', 'comments'], 'safe'],
            [['total_cost', 'total_advert_cost'], 'number'],
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
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Order::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'order_id' => $this->order_id,
            'offer_id' => $this->offer_id,
            'owner_id' => $this->owner_id,
            'customer_id' => $this->customer_id,
            'order_status' => $this->order_status,
            'delivery_date' => $this->delivery_date,
            'total_count' => $this->total_count,
            'total_cost' => $this->total_cost,
            'total_advert_cost' => $this->total_advert_cost,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'order_hash' => $this->order_hash,
        ]);

        $query->andFilterWhere(['like', 'comments', $this->comments]);

        return $dataProvider;
    }
}