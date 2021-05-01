<?php

namespace common\models\product;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ProductSearch represents the model behind the search form about `crm\models\product\Product`.
 */
class ProductSearch extends Product
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'created_by', 'updated_by', 'category'], 'integer'],
            [['product_name', 'created_at', 'updated_at'], 'safe'],
        ];
    }

//    /**
//     * @inheritdoc
//     */
//    public function scenarios()
//    {
////        $isWebMaster = Yii::$app->user->identity->role == 6;
////
////        if (!Yii::$app->user->can(Rbac::VIEW_ALL_OFFERS) && !$isWebMaster) {
////            $query->andWhere([self::tableName() . '.id' => OfferUser::findOffers()]);
////
////        }
//
//
//        // bypass scenarios() implementation in the parent class
////        return Model::scenarios();
//    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Product::find();

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
            'product_id' => $this->product_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'category' => $this->category,
        ]);

        $query->andFilterWhere(['like', 'product_name', $this->product_name]);

        return $dataProvider;
    }
}
