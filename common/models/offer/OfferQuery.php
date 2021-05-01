<?php

namespace common\models\offer;

/**
 * This is the ActiveQuery class for [[Offer]].
 *
 * @see Offer
 */
class OfferQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return Offer[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);  // Сюда ругается
    }

    /**
     * @inheritdoc
     * @return Offer|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
