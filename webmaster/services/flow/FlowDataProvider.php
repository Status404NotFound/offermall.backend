<?php

namespace webmaster\services\flow;

use Yii;
use common\models\order\OrderStatus;
use common\models\offer\Offer;
use common\models\flow\Flow;
use common\models\landing\Landing;
use common\models\offer\OfferTransit;
use common\models\flow\FlowLanding;
use common\models\flow\FlowTransit;
use common\models\webmaster\WmOffer;
use common\services\webmaster\Helper;
use webmaster\models\DataList;
use yii\db\ActiveQuery;

/**
 * Class FlowDataProvider
 * @package webmaster\services\flow
 */
class FlowDataProvider
{
    const approved_statuses = "(" .
    OrderStatus::WAITING_DELIVERY . ", " .
    OrderStatus::DELIVERY_IN_PROGRESS . ", " .
    OrderStatus::CANCELED . ", " .
    OrderStatus::NOT_PAID . ")";

    /**
     * @param array $filters
     * @param null $pagination
     * @param null $sort_field
     * @param null $sort_order
     * @return array
     */
    public function getWebmasterFlowList($filters = [], $pagination = null, $sort_order = null, $sort_field = null)
    {
        $query = Flow::find()
            ->select([
                'flow.flow_id',
                'flow.flow_name',
                'flow.flow_key',
//                'flow.advert_offer_target_status',
                "IF(flow.advert_offer_target_status IN ". self::approved_statuses .", 40, flow.advert_offer_target_status) as advert_offer_target_status",
                'flow.created_at',
                'offer.offer_name',
                'offer.offer_status',
                'user.username'
            ])
            ->join('LEFT JOIN', 'user', 'user.id = flow.wm_id')
            ->join('LEFT JOIN', 'customer', 'user.id = flow.wm_id')
            ->join('LEFT JOIN', 'offer', 'offer.offer_id = flow.offer_id')
            ->where(['flow.is_deleted' => 0])
            ->andWhere(['flow.wm_id' => Yii::$app->user->identity->getWmChild()]);

        if (isset($filters['flow_key']['value'])) $query->andFilterWhere(['LIKE', 'flow.flow_key', $filters['flow_key']['value']]);
        if (isset($filters['flow_name']['value'])) $query->andFilterWhere(['LIKE', 'flow.flow_name', $filters['flow_name']['value']]);
        if (isset($filters['offer_id'])) $query->andFilterWhere(['offer.offer_id' => $filters['offer_id']]);
        if (isset($filters['wm_id'])) $query->andWhere(['flow.wm_id' => $filters['wm_id']]);

//        if (isset($filters['advert_offer_target_status'])) {
//            $query->andFilterWhere(['flow.advert_offer_target_status' => $filters['advert_offer_target_status']]);
//        }

        if (isset($filters['advert_offer_target_status'])) {
            if ($filters['advert_offer_target_status'] == OrderStatus::WAITING_DELIVERY){
                $approved = [OrderStatus::DELIVERY_IN_PROGRESS, OrderStatus::WAITING_DELIVERY, OrderStatus::NOT_PAID, OrderStatus::CANCELED];
                $query->andFilterWhere(['flow.advert_offer_target_status' => $approved]);
            } else {
                $query->andFilterWhere(['flow.advert_offer_target_status' => $filters['advert_offer_target_status']]);
            }
        }

        if (isset($filters['time'])) {
            $start = new \DateTime($filters['time']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['time']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', 'flow.created_at', $start_date]);
            $query->andWhere(['<', 'flow.created_at', $end_date]);
        }

        if (isset($sort_field)) {
            $query->orderBy([$sort_field => $sort_order]);
        } else {
            $query->orderBy([
                '`flow`.created_at' => SORT_DESC
            ]);
        }

        $count = clone $query;
        $count_all = $count->count();

        if (isset($pagination)) $query->offset($pagination['first_row'])->limit($pagination['rows']);

        $flows_list = $query
            ->active()
            ->asArray()
            ->all();

        foreach ($flows_list as &$value) {
            $value['offer_status'] = DataList::getStatusLabel($value['offer_status']);
//            $value['target'] = OrderStatus::attributeLabels($value['advert_offer_target_status']);
            $value['target'] = ($value['advert_offer_target_status'] === '40') ? 'Approved' : OrderStatus::attributeLabels($value['advert_offer_target_status']);
        }

        return [
            'flows' => $flows_list,
            'count' => [
                'count_all' => $count_all
            ],
        ];
    }

    /**
     * @param $flow_id
     * @return array
     * @throws FlowNotFoundException
     */
    public function getFlow($flow_id)
    {
        $wm_flows = Flow::getWmFlowId();
        if (!in_array($flow_id, $wm_flows))
            throw new FlowNotFoundException('Permission denied.');

        $flow_query = Flow::find()
            ->select([
                'flow.flow_id',
                'flow.flow_key',
                'flow.flow_name',
                'flow.offer_id',
                'flow.active',
                'flow.use_tds',
                'flow.wm_id',
                'offer.offer_name',
                'offer.img',
//                'wm_offer_target.advert_offer_target_status',
                "IF(wm_offer_target.wm_offer_target_status IN ". self::approved_statuses .", 40, wm_offer_target.wm_offer_target_status) as advert_offer_target_status",
            ])
            ->join('LEFT JOIN', 'offer', 'offer.offer_id = flow.offer_id')
            ->join('LEFT JOIN', 'wm_offer_target', 'offer.offer_id = wm_offer_target.offer_id')
            ->where(['flow.wm_id' => Yii::$app->user->identity->getWmChild()])
            ->andWhere(['flow_id' => $flow_id])
            ->asArray()
            ->one();

//        $flow_query['advert_offer_target_name'] = OrderStatus::attributeLabels($flow_query['advert_offer_target_status']);
        $flow_query['advert_offer_target_name'] = ($flow_query['advert_offer_target_status'] === '40') ? 'Approved' : OrderStatus::attributeLabels($flow_query['advert_offer_target_status']);
        unset($flow_query['advert_offer_target_status']);

        return [
            'flow' => $flow_query,
            'offer_landings' => isset($flow_query['offer_id']) ? Landing::getOfferLandings($flow_query['offer_id']) : 'Not set',
            'offer_transits' => isset($flow_query['offer_id']) ? OfferTransit::getOfferTransits($flow_query['offer_id']) : 'Not set',
            'flow_landings' => isset($flow_query['flow_id']) ? FlowLanding::getFlowLandings($flow_query['flow_id']) : 'Not set',
            'flow_transits' => isset($flow_query['flow_id']) ? FlowTransit::getFlowTransits($flow_query['flow_id']) : 'Not set',
        ];
    }

    /**
     * @param $flow_id
     * @return array|Flow|null
     */
    public function getLink($flow_id)
    {
        $flow_link_query = Flow::find()
            ->select([
                'flow.flow_key',
                'flow.flow_name',
                'offer.offer_name',
                'landing.url'
            ])
            ->join('LEFT JOIN', 'offer', 'offer.offer_id = flow.offer_id')
            ->join('LEFT JOIN', 'flow_landing', 'flow.flow_id = flow_landing.flow_id')
            ->join('LEFT JOIN', 'landing', 'flow_landing.landing_id = landing.landing_id')
            ->where(['flow.is_deleted' => 0])
            ->andWhere(['flow.wm_id' => Yii::$app->user->identity->getWmChild()])
            ->andWhere(['flow.flow_id' => $flow_id])
            ->active()
            ->asArray()
            ->one();

        return $flow_link_query;
    }

    /**
     * @param $offer_id
     * @param null $wm_id
     * @return array
     */
    public function getOfferTargets($offer_id, $wm_id = null)
    {
        $uid = isset($wm_id) ? $wm_id : Yii::$app->user->identity->getId();
        $group_excepted = isset($wm_id) ? Helper::getWmExcepted($wm_id) : Helper::getWmExcepted();

        /* @var $flows ActiveQuery */
        $flows = (new Helper())->targetsQuery();

        $flows->where([
            'WOT.offer_id' => $offer_id,
            'TW.wm_id' => $uid,
            'TW.excepted' => 0,
            'TWG.active' => 1,
        ])
            ->orWhere(
                ['and', ['<>', 'TW.wm_id', $uid],
                    ['WOT.offer_id' => $offer_id, 'TW.excepted' => 1, 'TWG.active' => 1],]
            )
            ->orWhere(
                ['and', ['is', 'TW.wm_id', null],
                    ['WOT.offer_id' => $offer_id, 'TW.excepted' => 1, 'TWG.active' => 1],
                ]
            );

        if (!is_null($group_excepted)) {
            $flows->andFilterWhere(['not in', 'TWG.target_wm_group_id', $group_excepted]);
        }

        $query = clone $flows;

        $geo = $query
            ->asArray()
            ->all();

        $group = $query
            ->asArray()
            ->groupBy('wm_offer_target_status')
            ->all();

        $result = [];
        foreach ($group as $item) {
//            $status_name = OrderStatus::attributeLabels($item['wm_offer_target_status']);
            $status_name = ($item['wm_offer_target_status'] === '40') ? 'Approved' : OrderStatus::attributeLabels($item['wm_offer_target_status']);
//
            $geo_name = '';
            foreach ($geo as $countries) {
                if ($item['wm_offer_target_status'] == $countries['wm_offer_target_status']) {
                    $geo_name .= $countries['geo_name'] . ', ';
                }
            }

            $result[] = [
                'advert_offer_target_status' => $item['wm_offer_target_status'],
                'advert_offer_target_name' => $status_name . ' - ' . rtrim($geo_name, ', '),
            ];
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getOfferWmList()
    {
        return Offer::find()
            ->select([
                'offer.offer_id',
                'offer.offer_name'
            ])
            ->leftJoin('wm_offer', 'offer.offer_id = wm_offer.offer_id')
            ->where([
                'offer.offer_status' => Offer::STATUS_ACTIVE,
                'wm_offer.wm_id' => Yii::$app->user->identity->getWmChild(),
                'wm_offer.status' => WmOffer::STATUS_TAKEN
            ])
            ->asArray()
            ->all();
    }
}