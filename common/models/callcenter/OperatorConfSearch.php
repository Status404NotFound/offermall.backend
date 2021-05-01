<?php

namespace common\models\callcenter;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\callcenter\OperatorConf;
use common\services\callcenter\OperatorSettingsSrv;

class OperatorConfSearch extends OperatorConf
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['operator_id', 'call_mode', 'status', 'sip', 'channel'], 'integer'],
        ];
    }


    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }


    public function search($params)
    {
        $query = OperatorConf::find();
        // add conditions that should always apply here

        $query->select([
            'username',
            'operator_conf.*'
        ]);

        $query->join('INNER JOIN', '`user`', '`user`.id = operator_conf.operator_id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

//        if (!empty($parent_ids)) $query->andFilterWhere(['operator_id' => $parent_ids]);

        $this->operator_id = array_keys(OperatorSettingsSrv::getOperators());
        // grid filtering conditions
        $query->andFilterWhere([
            'operator_id' => $this->operator_id,
            'call_mode' => $this->call_mode,
            'status' => $this->status,
            'sip' => $this->sip,
            'channel' => $this->channel,
        ]);

        return $dataProvider;
    }
}
