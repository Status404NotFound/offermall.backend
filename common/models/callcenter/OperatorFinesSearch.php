<?php

namespace common\models\callcenter;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * OperatorActivitySearch represents the model behind the search form about `callcenter\models\OperatorFines`.
 */
class OperatorFinesSearch extends OperatorFine
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'operator_id'], 'integer'],
            ['is_active', 'boolean'],
            [['datetime'], 'string'],
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
        $query = OperatorFine::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        $this->load($params);
        isset($params['datetime']) ? $this->datetime = $params['datetime'] : $this->datetime = null;

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if (isset($this->datetime)){
            var_dump($this->datetime);
            $time_range = explode(' to ', $this->datetime);
            $from = $time_range[0];
            $to = $time_range[1];
            $query->andFilterWhere(['>', 'datetime', $from]);
            $query->andFilterWhere(['<', 'datetime', $to]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'operator_id' => $this->operator_id,
            'order_id' => $this->order_id,
            'is_active' => $this->is_active,
            //'datetime' => $this->datetime,
        ]);

        return $dataProvider;
    }
}
