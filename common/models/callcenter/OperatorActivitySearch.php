<?php

namespace common\models\callcenter;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\callcenter\OperatorActivity;

/**
 * OperatorActivitySearch represents the model behind the search form about `callcenter\models\OperatorActivity`.
 */
class OperatorActivitySearch extends OperatorActivity
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'operator_id', 'operator_status'], 'integer'],
            [['status_time_start', 'status_time_finish'], 'safe'],
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
        $query = OperatorActivity::find();
        $query->select([
            'username',
            'operator_id',
            'operator_status',
            'status_time_start',
            'status_time_finish',
        ]);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

//        var_dump($this->status_time_start);exit;
        $query->join('LEFT JOIN', 'user', 'user.id = operator_activity.operator_id');
        // grid filtering conditions
        $query->andFilterWhere([
            'operator_activity.id' => $this->id,
            'operator_activity.operator_id' => $this->operator_id,
            'operator_activity.operator_status' => $this->operator_status,
//            'status_time_start' => $this->status_time_start,
//            'status_time_finish' => $this->status_time_finish,
        ]);

        if (isset($this->status_time_start)) $query->andFilterWhere(['>', 'status_time_start',  $this->status_time_start]);
        if (isset($this->status_time_finish)) $query->andFilterWhere(['<', 'status_time_finish', $this->status_time_finish]);

        return $dataProvider;
    }
}
