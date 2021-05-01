<?php

namespace common\models\callcenter;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\callcenter\LeadCalls;

/**
 * LeadCallsSearch represents the model behind the search form about `common\models\callcenter\LeadCalls`.
 */
class LeadCallsSearch extends LeadCalls
{
    public $offer_id;
    public $offer_name;
    public $order_status;
    public $order_created_at;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'operator_id', 'order_id', 'call_id', 'duration', 'order_status', 'offer_id'], 'integer'],
            [['datetime', 'order_created_at'], 'safe'],
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
        $query = LeadCalls::find();
        $query->select([
            'lead_calls.*',

            'order.order_status',
            'order.created_at as order_created_at',

            'offer.offer_name',
            'offer.offer_id',
        ]);

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
        $query->join('LEFT JOIN', 'order', 'order.order_id = lead_calls.order_id');
        $query->join('LEFT JOIN', 'offer', 'offer.offer_id = order.offer_id');

        // grid filtering conditions
        $query->andFilterWhere([
            'lead_calls.id' => $this->id,
            'lead_calls.operator_id' => $this->operator_id,
            'lead_calls.order_id' => $this->order_id,
            'lead_calls.call_id' => $this->call_id,
            'lead_calls.duration' => $this->duration,
            'lead_calls.datetime' => $this->datetime,

            '`order`.order_status' => $this->order_status,
            '`order`.created_at' => $this->order_created_at,

            'offer.offer_id' => $this->offer_id,
        ]);

        return $dataProvider;
    }
}
