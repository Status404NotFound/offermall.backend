<?php

namespace common\models\callcenter;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\callcenter\OperatorLines;

/**
 * OperatorLinesSearch represents the model behind the search form about `common\models\callcenter\OperatorLines`.
 */
class OperatorLinesSearch extends OperatorLines
{
    public $operator_name;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'line_id', 'operator_id', 'is_active'], 'integer'],
            ['operator_name', 'string'],
            [['created_at', 'updated_at'], 'safe'],
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
        $query = OperatorLines::find();
        $query->select([
            'operator_lines.*',
            'user.username as operator_name',
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

        $query->join('INNER JOIN', 'user', 'operator_lines.operator_id = `user`.`id`');

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'line_id' => $this->line_id,
            'operator_id' => $this->operator_id,
            'is_active' => $this->is_active,
            'user.username' => $this->operator_name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        return $dataProvider;
    }
}
