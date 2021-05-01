<?php

namespace common\models\product;

use common\helpers\FishHelper;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the ActiveQuery class for [[Product]].
 *
 * @see Product
 */
class ProductQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    public function visible()
    {
        return $this->andWhere('[[visible]]=1');
    }

//    public function visible()
//    {
//        return $this->andWhere('[[visible]]=1');
//    }

    /**
     * @inheritdoc
     * @return Product[]|array
     */
    public function all($db = null)
    {
//        FishHelper::debug($db);

        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Product|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @inheritdoc
     * @return Product|array|null
     */
    public function productList($db = null)
    {
        $this->select('product_id, product_name')
            ->where([
                'visible' => 1,
            ])
            ->orderBy(['product_name' => SORT_ASC])
            ->asArray();

        return parent::all($db);
    }
}
