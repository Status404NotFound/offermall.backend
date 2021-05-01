<?php

namespace common\models\offer;

/**
 * This is the ActiveQuery class for [[OfferTransit]].
 *
 * @see OfferTransit
 */
class OfferTransitQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return OfferTransit[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return OfferTransit|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
