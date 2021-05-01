<?php

namespace common\models\offer\targets\wm;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[WmOfferTarget]].
 *
 * @see WmOfferTarget
 */
class WmOfferTargetQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return WmOfferTarget[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return WmOfferTarget|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}