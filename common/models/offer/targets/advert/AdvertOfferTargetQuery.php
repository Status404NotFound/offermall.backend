<?php

namespace common\models\offer\targets\advert;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[AdvertOfferTarget]].
 *
 * @see AdvertOfferTarget
 */
class AdvertOfferTargetQuery extends ActiveQuery
{
    public function active()
    {
        $this->andWhere('active = 1');
        return $this;
    }

    public function wm_visible()
    {
        $this->andWhere('wm_visible = 1');
        return $this;
    }

    /**
     * @inheritdoc
     * @return AdvertOfferTarget[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return AdvertOfferTarget|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

//    public function statusesByOfferId($db = null, $offer_id)
//    {
//        $this->select('advert_offer_target_status')
//            ->distinct()
//            ->where(['offer_id' => $offer_id, 'wm_visible' => 1, 'active' => 1])
//            ->groupBy('advert_offer_target_status, advert_offer_target_id')
//            ->asArray()
//            ->all();
//    }
//
//    /**
//     * @inheritdoc
//     * @return Product|array|null
//     */
//    public function productList($db = null)
//    {
//        $this->select('product_id, product_name')
//            ->where([
//                'visible' => 1,
//            ])
//            ->orderBy(['product_name' => SORT_ASC])
//            ->asArray();
//
//        return parent::all($db);
//    }
}