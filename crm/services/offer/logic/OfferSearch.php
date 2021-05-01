<?php

namespace crm\services\offer\logic;

use common\models\offer\Offer;
use common\models\offer\targets\advert\TargetAdvert;
use Yii;
use common\models\offer\OfferView;

use crm\services\targets\AdvertTargetService;
use crm\services\targets\logic\AdvertTargetDataProvider;
use common\models\product\SkuView;
use crm\services\offer\OfferLandingService;
use common\models\offer\targets\advert\TargetAdvertView;

/**
 * Class OfferService
 * @package crm\services\offer
 */
class OfferSearch
{
    /**
     * @param array $filters
     * @param null $pagination
     * @param null $sort_field
     * @param int $sortOrder
     * @return array|\yii\db\ActiveRecord[]
     */
    public function search($filters = [], $pagination = null, $sort_field = null, $sortOrder = SORT_DESC)
    {
        $offers = OfferView::find()->select([
            'offer_id',
            'offer_name',
            'offer_status',
            'description',
            'img',
            '(SELECT SUM(IF(ta_active = 1, 1, 0)) AS temp ) as active',
        ]);

        if (!is_null(Yii::$app->user->identity->getOwnerId()))
            $offers->where(['advert_id' => Yii::$app->user->identity->getOwnerId()]);

        if (isset($filters['offer_status'])) $offers->andWhere(['in', 'offer_status', $filters['offer_status']['value']]);
        if (isset($filters['offer_id'])) $offers->andWhere(['offer_id' => $filters['offer_id']['value']]);
        if (isset($filters['country_id'])) $offers->andWhere(['geo_id' => $filters['country_id']['value']]);
        if (isset($filters['advert_id'])) $offers->andWhere(['advert_id' => $filters['advert_id']['value']]);

        if (isset($filters['active'])) {
            if ($filters['active']['value'] == '1') {
                $offers->andFilterHaving(['>=', 'active', 1]);
            } else {
                $offers->andFilterHaving(['=', 'active', 0]);
            }
        }

        $offers->groupBy('offer_id');
        if (!isset($pagination)) {
            return $offers->asArray()->all();
        }
        $count = clone $offers;
        $count = $count->count();

//        $sort_order = ($sort_order == -1) ? SORT_DESC : SORT_ASC;
//        if (isset($sort_field)) $offers->orderBy([$sort_field => $sort_order]);

        $offers->orderBy(['offer_id' => $sortOrder]);

        return [
            'offers' => $offers->offset($pagination['first_row'])
                ->limit($pagination['rows'])
                ->asArray()
                ->all(),
            'count' => $count
        ];
    }

    public function offersInfo($filters = [], $pagination = null, $sort_field = null, $sort_order = SORT_ASC)
    {
        $query = OfferView::find()->select(['offer_id', 'offer_name', 'offer_status', 'product_id']);

        if (!is_null(Yii::$app->user->identity->getOwnerId())) $query->where(['advert_id' => Yii::$app->user->identity->getOwnerId()]);

        if (isset($filters['offer_status'])) $query->andWhere(['in', 'offer_status', $filters['offer_status']['value']]);
        if (isset($filters['offer_id'])) $query->andWhere(['offer_id' => $filters['offer_id']['value']]);
        if (isset($filters['geo_id'])) $query->andWhere(['geo_id' => $filters['geo_id']['value']]);
        if (isset($filters['advert_id'])) $query->andWhere(['advert_id' => $filters['advert_id']['value']]);

        $query->groupBy('offer_id');

        if (!isset($pagination)) return $query->asArray()->all();

        $count = clone $query;
        $count = $count->count();

        $sort_order = ($sort_order == -1) ? SORT_DESC : SORT_ASC;
        if (isset($sort_field)) $query->orderBy([$sort_field => $sort_order]);

        $query = [
            'offers' => $query->offset($pagination['first_row'])
                ->limit($pagination['rows'])
                ->asArray()
                ->all(),
            'count' => $count
        ];

        foreach ($query['offers'] as &$offer) {
            $offer['targets'] = (new AdvertTargetService())->getAdvertTargetData($offer['offer_id'], AdvertTargetDataProvider::SKU_TAB);
            $offer['sku_list'] = SkuView::find()->select(['sku_id', 'sku_name', 'geo_id', 'advert_id'])->where(['product_id' => $offer['product_id']])->asArray()->all();
            $offerLandingService = new OfferLandingService($offer['offer_id']);
            $offer['landings'] = $offerLandingService->getLandings();
            $offer['geo_price'] = $offerLandingService->getOfferGeoPrice();
            $offer['ladings_list'] = SkuView::find()->select(['sku_id', 'sku_name', 'geo_id', 'advert_id'])->where(['product_id' => $offer['product_id']])->asArray()->all();
        }

        return $query;
    }
}