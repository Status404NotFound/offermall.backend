<?php

namespace webmaster\services\offer;

use Yii;
use common\models\offer\Offer;
use common\models\order\OrderStatus;
use common\models\offer\targets\wm\WmOfferTarget;
use common\models\webmaster\WmOffer;
use common\services\webmaster\Helper;
use common\services\offer\exceptions\WmServiceException;
use webmaster\models\DataList;
use yii\helpers\ArrayHelper;
use yii\db\ActiveQuery;

/**
 * Class OfferSearch
 * @package webmaster\services\offer
 */
class OfferSearch
{
    const approved_statuses = "(" .
            OrderStatus::WAITING_DELIVERY . ", " .
            OrderStatus::DELIVERY_IN_PROGRESS . ", " .
            OrderStatus::CANCELED . ", " .
            OrderStatus::NOT_PAID . ")";

    /**
     * @param array $filters
     * @param null $pagination
     * @param null $sort_order
     * @param null $sort_field
     * @return array
     */
    public function getOffersList($filters = [], $pagination = null, $sort_order = null, $sort_field = null)
    {
        $group_excepted = Helper::getWmExcepted();

        $offers = Offer::find()
            ->select([
                'offer.offer_id',
                'offer.offer_status',
                'offer.offer_name',
                'offer.img',
            ])
            ->joinWith(['wmOfferTargets' => function (ActiveQuery $query){
                $query->select([
                    'wm_offer_target.offer_id',
//                    'wm_offer_target_status',
                    "IF(wm_offer_target_status IN ". self::approved_statuses .", 40, wm_offer_target_status) as wm_offer_target_status",
                    'wm_offer_target.wm_offer_target_id',
                    'wm_offer.wm_id',
                    'wm_offer.status',
                    'geo.geo_name',
                    'geo.iso',
                ])
                    ->join('LEFT JOIN', 'wm_offer', 'wm_offer.offer_id = wm_offer_target.offer_id')
                    ->join('LEFT JOIN', 'geo', 'geo.geo_id = wm_offer_target.geo_id')
                    ->joinWith(['targetWmGroups' => function (ActiveQuery $query) {
                        $query->select([
                            'target_wm_group.wm_offer_target_id',
                            'target_wm_group.target_wm_group_id',
                            'target_wm_group.hold',
                            'target_wm_group.active',
                            'target_wm_group.view_for_all'
                        ])
                            ->joinWith(['targetWms' => function (ActiveQuery $query) {
                                $query->select([
                                    'target_wm.target_wm_id',
                                    'target_wm.target_wm_group_id',
                                    'target_wm.wm_id',
                                    '`user`.username as wm_name',
                                    'target_wm.excepted',
                                    'target_wm.active'
                                ])
                                    ->leftJoin('target_wm_group TWG', 'TWG.target_wm_group_id = target_wm.target_wm_group_id')
                                    ->leftJoin('`user`', '`user`.id = target_wm.wm_id')
                                    ->where([
                                        'target_wm.wm_id' => Yii::$app->user->identity->getWmChild(),
                                        'target_wm.excepted' => 0,
                                        'TWG.active' => 1,
                                    ])
                                    ->orWhere(
                                        ['and', ['<>', 'target_wm.wm_id', Yii::$app->user->identity->getWmChild()],
                                            ['target_wm.excepted' => 1, 'TWG.active' => 1],]
                                    )
                                    ->orWhere(
                                        ['and', ['is', 'target_wm.wm_id', null],
                                            ['target_wm.excepted' => 1, 'TWG.active' => 1],
                                        ]
                                    );
                            }]);
                    }]);
            }]);

        if (!is_null($group_excepted)) {
            $offers->andFilterWhere(['not in', 'target_wm_group.target_wm_group_id', $group_excepted]);
        }

        if (isset($filters['my'])) {
            $offers->where([
                'wm_offer.wm_id' => Yii::$app->user->identity->getWmChild(),
                'wm_offer.status' => WmOffer::STATUS_TAKEN,
            ]);
        } else {
            $offers->where(['offer.offer_status' => Offer::STATUS_ACTIVE])->orWhere(['offer.offer_status' => Offer::STATUS_ON_PAUSE]);
        }

        if (isset($filters['offer_id'])) $offers->andFilterWhere(['offer.offer_id' => $filters['offer_id']]);
        if (isset($filters['wm_id'])) $offers->andFilterWhere(['target_wm.wm_id' => $filters['wm_id']]);

        if (isset($filters['advert_offer_target_status'])) {
            if (in_array(OrderStatus::WAITING_DELIVERY, $filters['advert_offer_target_status'])){
                $approved = [OrderStatus::DELIVERY_IN_PROGRESS, OrderStatus::WAITING_DELIVERY, OrderStatus::NOT_PAID, OrderStatus::CANCELED];
                $offers->andFilterWhere(['wm_offer_target.wm_offer_target_status' => $approved]);
            } else {
                $offers->andFilterWhere(['wm_offer_target.wm_offer_target_status' => $filters['advert_offer_target_status']]);
            }
        }

        if (isset($filters['geo_id'])) $offers->andFilterWhere(['wm_offer_target.geo_id' => $filters['geo_id']]);

        if (isset($sort_field)) {
            $offers->orderBy([$sort_field => $sort_order]);
        } else {
            $offers->orderBy([
                'offer.offer_id' => SORT_DESC,
            ]);
        }

        $count = clone $offers;
        $count_all = $count->groupBy('offer.offer_id')->count();

        if (isset($pagination)) $offers->offset($pagination['first_row'])->limit($pagination['rows']);

        $offers_list = $offers
            ->groupBy(['wm_offer_target.offer_id'])
            ->asArray()
            ->all();

        $result = [];
        foreach ($offers_list as $key => $list) {
            $result[$key] = [
                'offer_id' => $list['offer_id'],
                'offer_name' => $list['offer_name'],
                'img' => $list['img'],
                'offer_status' => DataList::getStatusLabel($list['offer_status']),
                'webmaster' => 0
            ];
            foreach ($list['wmOfferTargets'] as $offer_targets) {
                foreach ($offer_targets['targetWmGroups'] as $target) {
                    foreach ($target['targetWms'] as $wm) {
                        $result[$key]['targets'][] = [
                            'advert_offer_target_status' => $offer_targets['wm_offer_target_status'],
                            'advert_offer_target_name' => ($offer_targets['wm_offer_target_status'] === '40') ? 'Approved' : OrderStatus::attributeLabels($offer_targets['wm_offer_target_status']),
                            'geo_name' => $offer_targets['geo_name'],
                            'iso' => $offer_targets['iso'],
                            'hold' => $target['hold'],
                            'active' => $target['active'],
                            'view_for_all' => $target['view_for_all'],
                            'wm_id' => $wm['wm_id'],
                            'wm_name' => $wm['wm_name'],
                            'commission' => (new Helper())->getWmRules($wm['target_wm_id']),
                        ];

                        usort($result[$key]['targets'], function ($a, $b) {
                            return $a['advert_offer_target_status'] < $b['advert_offer_target_status'];
                        });
                    }
                }
            }
        }

        $models = $this->offerWm($result, WmOffer::getWebmaster());

        return [
            'offers' => array_values($models),
            'count' => [
                'count_all' => $count_all,
            ],
        ];
    }

    /**
     * @param $offer_id
     * @return array
     * @throws WmServiceException
     */
    public function getOfferInfoData($offer_id)
    {
//        $wm_offer_id_array = Offer::getWmOfferIdArray();
//        if (!in_array($offer_id, $wm_offer_id_array))
//            throw new WmServiceException('Permission denied.');

        $query = Offer::find()
            ->select([
                'offer.offer_id',
                'offer.offer_name',
                'offer.description',
                'offer.img',
                'offer.offer_status',
                'wm_offer.status as wm_offer_status',
            ])
            ->join('LEFT JOIN', 'wm_offer', 'wm_offer.offer_id = offer.offer_id AND wm_offer.`status` = ' . WmOffer::STATUS_TAKEN)
            ->joinWith(['landings' => function (ActiveQuery $query) {
                $query->select(['landing.offer_id', 'landing.name', 'landing.url']);
            }])
            ->where(['offer.offer_id' => $offer_id])
            ->andWhere(['offer.offer_status' => Offer::STATUS_ACTIVE])
            ->groupBy(['offer.offer_id'])
            ->asArray()
            ->one();

        return [
            'targets' => $this->normalizeOfferTargetsViewById($offer_id),
            'offer_landings' => $query['landings'],
            'offer_name' => $query['offer_name'],
            'status' => DataList::getStatusLabel($query['offer_status']),
            'image' => $query['img'],
            'description' => $query['description'],
            'taken' => (int)$query['wm_offer_status'] ?? 0
        ];
    }

    /**
     * @param $offer_id
     * @return array
     */
    private function normalizeOfferTargetsViewById($offer_id)
    {
        $group_excepted = Helper::getWmExcepted();

        $offers = WmOfferTarget::find()
            ->select([
                'wm_offer_target.offer_id',
//                'wm_offer_target.wm_offer_target_status',
                "IF(wm_offer_target.wm_offer_target_status IN ". self::approved_statuses .", 40, wm_offer_target.wm_offer_target_status) as wm_offer_target_status",
                'wm_offer_target.wm_offer_target_id',
                'geo.geo_id',
                'geo.geo_name',
                'geo.iso',
                'offer_geo_price.new_price as price'
            ])
            ->join('LEFT JOIN', 'geo', 'geo.geo_id = wm_offer_target.geo_id')
            ->join('LEFT JOIN', 'offer_geo_price', 'offer_geo_price.offer_id = wm_offer_target.offer_id AND wm_offer_target.geo_id = offer_geo_price.geo_id')
            ->joinWith(['targetWmGroups' => function (ActiveQuery $query) {
                $query->select([
                    'target_wm_group.wm_offer_target_id',
                    'target_wm_group.target_wm_group_id',
                    'target_wm_group.hold'
                ])
                    ->joinWith(['targetWms' => function (ActiveQuery $query) {
                        $query->select([
                            'target_wm.target_wm_id',
                            'target_wm.target_wm_group_id'
                        ])
                            ->leftJoin('target_wm_group TWG', 'TWG.target_wm_group_id = target_wm.target_wm_group_id')
                            ->where([
                                'target_wm.wm_id' => Yii::$app->user->identity->getId(),
                                'target_wm.excepted' => 0,
                                'TWG.active' => 1,
                            ])
                            ->orWhere(
                                ['and', ['<>', 'target_wm.wm_id', Yii::$app->user->identity->getId()],
                                    ['target_wm.excepted' => 1, 'TWG.active' => 1],]
                            )
                            ->orWhere(
                                ['and', ['is', 'target_wm.wm_id', null],
                                    ['target_wm.excepted' => 1, 'TWG.active' => 1],
                                ]
                            );
                    }]);
            }])
            ->where(['wm_offer_target.offer_id' => $offer_id]);

        if (!is_null($group_excepted)) {
            $offers->andFilterWhere(['not in', 'target_wm_group.target_wm_group_id', $group_excepted]);
        }

        $targets_list = $offers
            ->asArray()
            ->all();

        $result = [];
        foreach ($targets_list as $key => $targets) {
            foreach ($targets['targetWmGroups'] as $target) {
                foreach ($target['targetWms'] as $wm) {
                    $result[$key] = [
//                        'target' => OrderStatus::attributeLabels($targets['wm_offer_target_status']),
                        'target' => ($targets['wm_offer_target_status'] === '40') ? 'Approved' : OrderStatus::attributeLabels($targets['wm_offer_target_status']),
                        'geo_name' => $targets['geo_name'],
                        'iso' => $targets['iso'],
//                        'landing_price' => (new OfferService())->getPriceOnLanding($offer_id, $targets['geo_id']),
                        'landing_price' => $targets['price'],
                        'hold' => $target['hold'],
                        'commission' => (new Helper())->getWmRules($wm['target_wm_id']),
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * @param $offers
     * @param $webmasters
     * @return array
     */
    private function offerWm($offers, $webmasters)
    {
        $offers = ArrayHelper::index($offers, 'offer_id');

        foreach ($webmasters as $webmaster) {
            $index = $webmaster['offer_id'];
            if (isset($offers[$index])) {
                $offers[$index]['webmaster'] = $webmaster;
            }
        }

        return $offers;
    }
}