<?php

namespace common\models\callcenter;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\callcenter\PhoneLines;

/**
 * PhoneLinesSearch represents the model behind the search form about `common\models\callcenter\PhoneLines`.
 */
class PhoneLinesSearch extends PhoneLines
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'owner_id', 'line', 'country_id', 'asterisk_id'], 'integer'],
            [['owner_name', 'country_name', 'country_code'], 'string'],
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
        $query = PhoneLines::find();
        $query->select([
            'phone_lines.*',
            'user.username as owner_name',
            'countries.country_name',
            'countries.country_code',
            'countries.id as country_id',
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
        $query->join('LEFT JOIN', 'user', 'user.id = phone_lines.owner_id');
        $query->join('LEFT JOIN', 'countries', 'countries.id = phone_lines.country_id');
        // grid filtering conditions
        if ($this->owner_id != null)$query->andFilterWhere(['owner_id' => $this->owner_id]);
        $query->andFilterWhere([
            'id' => $this->id,
            //'owner_id' => $this->owner_id,
            'line' => $this->line,
            'country_id' => $this->country_id,
            'asterisk_id' => $this->asterisk_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        return $dataProvider;
    }
}
