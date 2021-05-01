<?php

namespace common\models\stock;

use common\helpers\FishHelper;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * StockSkuearch represents the model behind the search form about `common\models\stock_sku\StockSku`.
 */
class StockSkuSearch extends StockSku
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['stock_id', 'sku_id', 'sku_name', 'count', 'updated_by'], 'integer'],
            [['updated_at'], 'safe'],
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
        $query = StockSku::find();

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

//        $query->rightJoinProductSku();
        // grid filtering conditions
        $query->andFilterWhere([
            'stock_id' => $this->stock_id,
            'sku_id' => $this->sku_id,
            'sku_name' => $this->sku_name,
            'count' => $this->count,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        return $dataProvider;
    }
}
