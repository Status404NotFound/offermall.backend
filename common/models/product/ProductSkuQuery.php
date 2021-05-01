<?php

namespace common\models\product;

/**
 * This is the ActiveQuery class for [[ProductSku]].
 *
 * @see ProductSku
 */
class ProductSkuQuery extends \yii\db\ActiveQuery
{
    public function visible()
    {
        return $this->andWhere('[[visible]]=1');
    }

    public function skuInStock($stock_id)
    {
        return $this->andFilterWhere(['ss.sku_id' => $stock_id]);
    }

    public function notInStock($stock_id = null)
    {
        return $this->andFilterWhere(['not', ['ss.sku_id' => $stock_id]]);
    }

    public function leftJoinStockSku()
    {
        return $this->from('product_sku as ps')
            ->select([
                'ps.sku_id as sku_id',
                'ps.sku_name',
                'ss.sku_id as stock_sku_id',
                'ss.stock_id',
                'ss.count',
            ])
            ->join('LEFT JOIN',
                'stock_sku as ss', [
                    'ss.sku_id' => 'ps.sku_id'
                ]);
    }

    /**
     * @inheritdoc
     * @return ProductSku|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @inheritdoc
     * @return ProductSku[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ProductSku|array|null
     */
    public function skuList($product_id, $db = null)
    {
        $this->select('sku_id, product_id, sku_name')
            ->where([
                'visible' => 1,
                'product_id' => $product_id,
            ])
//            ->indexBy('sku_id')
            ->orderBy(['sku_name' => SORT_ASC])
            ->asArray();

        return parent::all($db);
    }


}
