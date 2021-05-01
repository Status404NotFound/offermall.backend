<?php

namespace common\models\offer\targets\advert\sku;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[TargetAdvertSku]].
 *
 * @see TargetAdvertSku
 */
class TargetAdvertSkuQuery extends ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return TargetAdvertSku[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return TargetAdvertSku|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}