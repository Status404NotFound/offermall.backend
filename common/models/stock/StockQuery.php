<?php

namespace common\models\stock;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Stock]].
 *
 * @see Stock
 */
class StockQuery extends ActiveQuery
{
    /**
     * @return $this
     */
    public function active()
    {
        return $this->andWhere('[[status]]=10');
    }

    /**
     * @inheritdoc
     * @return Stock[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Stock|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @inheritdoc
     * @return Stock|array|null
     */
    public function list($db = null)
    {
        $this->select('stock_id, stock_name')
            ->active()
            ->asArray();
        return parent::all($db);
    }


    /**
     * @param null $db
     * @return $this
     */
    public function withSku($db = null)
    {
        return $this->with(['stock_sku' => function (ActiveQuery $stockSkus) {
            $stockSkus->select([
                'stock_id',
                'stock_sku.sku_id',
                'amount',
                'sku_name',
                'product_name'
            ])
                ->join('INNER JOIN', 'product_sku PS', 'PS.sku_id = stock_sku.sku_id')
                ->join('INNER JOIN', 'product P', 'P.product_id = PS.product_id');
        }]);
    }
}
