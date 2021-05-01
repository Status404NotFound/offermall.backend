<?php

namespace common\models\callcenter;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * OperatorPcsSearch represents the model behind the search form about `frontend\models\OperatorPcs`.
 */
class OperatorPcsSearch extends OperatorPcs
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['operator_pcs_id', 'operator_id', 'order_id', 'pcs_old', 'pcs_new', 'up_sale'], 'integer'],
            [['created_at'], 'safe'],
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
        $query = OperatorPcs::find();

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
            'operator_pcs_id' => $this->operator_pcs_id,
            'operator_id' => $this->operator_id,
            'order_id' => $this->order_id,
            'pcs_old' => $this->pcs_old,
            'pcs_new' => $this->pcs_new,
            'up_sale' => $this->up_sale,
            'created_at' => $this->created_at,
        ]);

        return $dataProvider;
    }
}
