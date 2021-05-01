<?php

namespace common\models\order;

use yii\db\ActiveQuery;


/**
 * This is the ActiveQuery class for [[OrderView]].
 *
 * @see OrderView
 */
class OrderViewQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return OrderView[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return OrderView|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}