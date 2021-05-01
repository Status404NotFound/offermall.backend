<?php

namespace webmaster\models;

use Yii;
use yii\base\Model;
use common\modules\user\models\tables\BaseProfile;
use common\models\offer\Offer;
use common\models\offer\targets\wm\TargetWm;
use common\models\offer\targets\wm\TargetWmGroup;
use common\models\offer\targets\advert\TargetAdvert;
use common\models\flow\Flow;
use common\modules\user\models\tables\User;
use common\models\order\OrderStatus;
use common\services\webmaster\Helper;
use yii\helpers\ArrayHelper;

/**
 * Class DataList
 * @package webmaster\models
 */
class DataList extends Model
{
    /**
     * @return array
     */
    public function getWmStatuses()
    {
        $statuses = [
            ['advert_offer_target_status' => OrderStatus::PENDING, 'advert_offer_target_name' => 'Pending'],
            ['advert_offer_target_status' => OrderStatus::WAITING_DELIVERY, 'advert_offer_target_name' => 'Approved'],
            ['advert_offer_target_status' => OrderStatus::SUCCESS_DELIVERY, 'advert_offer_target_name' => 'Success Delivery'],
        ];

        return $statuses;
    }

    /**
     * @inheritdoc
     */
    public static function getStatusLabel($status_id = null)
    {
        $statuses = [
            Offer::STATUS_ON_PAUSE => Yii::t('app', 'On pause'),
            Offer::STATUS_ACTIVE => Yii::t('app', 'Active'),
            Offer::STATUS_ARCHIVED => Yii::t('app', 'Archived'),
        ];

        return isset($status_id) ? $statuses[$status_id] : $statuses;
    }

    /**
     * @return array|\common\models\geo\Geo[]|TargetWm[]|User[]|\yii\db\ActiveRecord[]
     */
    public function offerList()
    {
        $wm_offers = TargetWm::find()
            ->select('offer.offer_id, offer.offer_name')
            ->join('LEFT JOIN', 'target_wm_group', 'target_wm.target_wm_group_id = target_wm_group.target_wm_group_id')
            ->join('LEFT JOIN', 'wm_offer_target', 'target_wm_group.wm_offer_target_id = wm_offer_target.wm_offer_target_id')
            ->join('LEFT JOIN', 'offer', 'offer.offer_id = wm_offer_target.offer_id')
            ->innerJoin('wm_offer', 'offer.offer_id = wm_offer.offer_id')
            ->where(['offer.offer_status' => Offer::STATUS_ACTIVE])
            ->andWhere([
                'target_wm.wm_id' => Yii::$app->user->identity->getId(),
                'wm_offer.wm_id' => Yii::$app->user->identity->getId(),
            ])
            ->groupBy('offer.offer_id')
            ->asArray()
            ->all();

        return $wm_offers;
    }

    /**
     * @return array|\common\models\finance\Currency[]|\common\models\flow\FlowLanding[]|\common\models\flow\FlowTransit[]|\common\models\geo\Countries[]|\common\models\geo\Geo[]|\common\models\geo\GeoRegion[]|\common\models\landing\Landing[]|\common\models\LandingViews[]|\common\models\offer\targets\advert\TargetAdvert[]|TargetWm[]|\common\models\order\OrderView[]|User[]|\yii\db\ActiveRecord[]
     */
    public function getWebmasterList()
    {
        $query = User::find()
            ->select('`id` as user_id, username as user_name')
            ->join('LEFT JOIN', 'user_child', 'user_child.child = user.id');

        if (!is_null(Yii::$app->user->identity->getWmChild())) {
            $query->andWhere(['user.id' => Yii::$app->user->identity->getWmChild()]);
        } else $query->andWhere(['user.id' => Yii::$app->user->identity->getId()]);

        $webmasters = $query
            ->groupBy('user.id')
            ->asArray()
            ->all();

        return $webmasters;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getWebmasterGeo()
    {
        return TargetWm::find()
            ->select([
                'geo.iso',
                'geo.geo_name',
                'wm_offer_target.geo_id'
            ])
            ->leftJoin('target_wm_group', 'target_wm_group.target_wm_group_id = target_wm.target_wm_group_id')
            ->leftJoin('wm_offer_target', 'target_wm_group.wm_offer_target_id = wm_offer_target.wm_offer_target_id')
            ->leftJoin('geo', 'wm_offer_target.geo_id = geo.geo_id')
            ->where(['target_wm.wm_id' => Yii::$app->user->identity->getId()])
            ->asArray()
            ->groupBy('geo.id')
            ->all();
    }

    /**
     * @return array|\common\models\finance\Currency[]|\common\models\geo\Countries[]|\common\models\geo\Geo[]|\common\models\geo\GeoRegion[]|\common\models\offer\targets\advert\TargetAdvert[]|TargetWm[]|TargetWmGroup[]|User[]|\yii\db\ActiveRecord[]
     */
    public function getWebmasterGeoWithParent()
    {
        return TargetWm::find()
            ->select([
                'geo.iso',
                'geo.geo_name',
                'wm_offer_target.geo_id'
            ])
            ->leftJoin('target_wm_group', 'target_wm_group.target_wm_group_id = target_wm.target_wm_group_id')
            ->leftJoin('wm_offer_target', 'target_wm_group.wm_offer_target_id = wm_offer_target.wm_offer_target_id')
            ->leftJoin('geo', 'wm_offer_target.geo_id = geo.geo_id')
            ->where(['target_wm.wm_id' => Yii::$app->user->identity->getWmChild()])
            ->asArray()
            ->groupBy('geo.id')
            ->all();
    }

    /**
     * @param $flow_id
     * @return array|\common\models\geo\Geo[]|TargetWm[]|TargetWmGroup[]|\common\models\offer\targets\wm\TargetWmGroupRules[]|User[]|\yii\db\ActiveRecord[]
     */
    public function getWebmasterFlowGeo($flow_id)
    {
        $flow_data = Flow::findOne(['flow_id' => $flow_id]);

        $group_excepted = Helper::getWmExcepted();

        $query = TargetWmGroup::find()
            ->select([
                'geo.geo_name',
                'geo.geo_id',
                'geo.iso'
            ])
            ->leftJoin('wm_offer_target', 'wm_offer_target.wm_offer_target_id = target_wm_group.wm_offer_target_id')
            ->leftJoin('target_wm', 'target_wm.target_wm_group_id = target_wm_group.target_wm_group_id')
            ->leftJoin('geo', 'wm_offer_target.geo_id = geo.geo_id')
            ->where([
                'wm_offer_target.offer_id' => $flow_data->offer_id,
                'wm_offer_target.advert_offer_target_status' => $flow_data->advert_offer_target_status,
                'target_wm.wm_id' => $flow_data->wm_id,
                'target_wm.excepted' => 0,
                'target_wm_group.active' => 1,
            ])
            ->orWhere(
                ['and', ['<>', 'target_wm.wm_id', $flow_data->wm_id],
                    ['wm_offer_target.offer_id' => $flow_data->offer_id, 'target_wm.excepted' => 1, 'target_wm_group.active' => 1],]
            )
            ->orWhere(
                ['and', ['is', 'target_wm.wm_id', null],
                    ['wm_offer_target.offer_id' => $flow_data->offer_id, 'target_wm.excepted' => 1, 'target_wm_group.active' => 1],
                ]
            );

        if (!is_null($group_excepted)) {
            $query->andFilterWhere(['not in', 'target_wm_group.target_wm_group_id', $group_excepted]);
        }

        $result = $query
            ->asArray()
            ->all();

        return $result;
    }

    /**
     * @return array|Offer[]
     */
    public function getWmOffersWithParent()
    {
        $offers_query = TargetWm::find()
            ->select('offer.offer_id, offer.offer_name')
            ->join('LEFT JOIN', 'target_wm_group', 'target_wm.target_wm_group_id = target_wm_group.target_wm_group_id')
            ->join('LEFT JOIN', 'wm_offer_target', 'target_wm_group.wm_offer_target_id = wm_offer_target.wm_offer_target_id')
            ->join('LEFT JOIN', 'offer', 'offer.offer_id = wm_offer_target.offer_id')
            ->join('LEFT JOIN', 'wm_offer', 'offer.offer_id = wm_offer.offer_id')
            ->where(['offer.offer_status' => Offer::STATUS_ACTIVE])
            ->andWhere(['target_wm.wm_id' => Yii::$app->user->identity->getWmChild()])
            ->andWhere(['wm_offer.wm_id' => Yii::$app->user->identity->getWmChild()])
            ->orWhere(['offer.offer_status' => Offer::STATUS_ON_PAUSE])
            ->andWhere(['target_wm.wm_id' => Yii::$app->user->identity->getWmChild()])
            ->andWhere(['wm_offer.wm_id' => Yii::$app->user->identity->getWmChild()])
            ->groupBy('offer.offer_id')
            ->asArray()
            ->all();

        return $offers_query;
    }

    /**
     * @param null $wm_id
     * @return array|\common\models\geo\Geo[]|TargetWm[]|User[]|\yii\db\ActiveRecord[]
     */
    public function getWmOffers($wm_id = null)
    {
        $offers_query = TargetWm::find()
            ->select('offer.offer_id, offer.offer_name')
            ->join('LEFT JOIN', 'target_wm_group', 'target_wm.target_wm_group_id = target_wm_group.target_wm_group_id')
            ->join('LEFT JOIN', 'wm_offer_target', 'target_wm_group.wm_offer_target_id = wm_offer_target.wm_offer_target_id')
            ->join('LEFT JOIN', 'offer', 'offer.offer_id = wm_offer_target.offer_id')
            ->join('LEFT JOIN', 'wm_offer', 'offer.offer_id = wm_offer.offer_id')
            ->where(['offer.offer_status' => Offer::STATUS_ACTIVE])
            ->andWhere(['target_wm_group.active' => 1])
            ->andWhere(['target_wm.active' => 1]);

        if (isset($wm_id)) {
            $offers_query->andWhere([
                'target_wm.wm_id' => $wm_id,
                'wm_offer.wm_id' => $wm_id,
            ]);
        } else {
            $offers_query->andWhere([
                'target_wm.wm_id' => Yii::$app->user->identity->getWmChild(),
                'wm_offer.wm_id' => Yii::$app->user->identity->getWmChild(),
            ]);
        }

        $wm_offers = $offers_query
            ->groupBy('offer.offer_id')
            ->asArray()
            ->all();

        return $wm_offers;
    }

    /**
     * @return array|\common\models\finance\Currency[]|\common\models\geo\Countries[]|\common\models\geo\Geo[]|\common\models\geo\GeoRegion[]|TargetAdvert[]|TargetWm[]|TargetWmGroup[]|User[]|\yii\db\ActiveRecord[]
     */
    public function getOfferAdverts()
    {
        $wm_offers = $this->getWmOffersWithParent();

        $wm_offers_array = [];
        foreach ($wm_offers as $k => $wm_offer) {
            $wm_offers_array[] = $wm_offer['offer_id'];
        }

        $adverts_query = TargetAdvert::find()
            ->select('target_advert.advert_id, user.username')
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert.target_advert_group_id = target_advert_group.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->join('LEFT JOIN', 'user', 'user.id = target_advert.advert_id')
            ->where(['advert_offer_target.offer_id' => $wm_offers_array])
            ->groupBy('target_advert.advert_id')
            ->asArray()
            ->all();

        return $adverts_query;
    }

    /**
     * @param array $request
     * @return array|Flow[]
     */
    public function getWmOfferFlows(array $request)
    {
        $query = Flow::find()
            ->select(['flow.flow_id', 'flow.flow_name'])
            ->where(['active' => Flow::STATUS_ACTIVE])
            ->andWhere(['flow.is_deleted' => 0])
            ->andWhere(['offer_id' => $request['offer_id']]);

        if (isset($request['wm_id'])) $query->andWhere(['flow.wm_id' => $request['wm_id']]);

        $wm_offer_flows = $query
            ->asArray()
            ->all();

        return $wm_offer_flows;
    }

    /**
     * @param null $wm_id
     * @return array|Flow[]
     */
    public function getFlows($wm_id = null)
    {
        $flows = Flow::find()
            ->select(['flow_id', 'flow_name'])
            ->where(['active' => Flow::STATUS_ACTIVE]);

        if (isset($wm_id)) $flows->andWhere(['wm_id' => $wm_id]);

        $query = $flows
            ->asArray()
            ->all();

        return $query;
    }

    /**
     * @return array
     */
    public function timeZoneList()
    {
        $timeZones = [];
        $timeZoneIdentifiers = \DateTimeZone::listIdentifiers();

        foreach ($timeZoneIdentifiers as $timeZone) {
            $date = new \DateTime('now', new \DateTimeZone($timeZone));
            $offset = $date->getOffset() / 60 / 60;
            $timeZones[] = [
                'timezone' => $timeZone,
                'name' => "{$timeZone} (UTC " . ($offset > 0 ? '+' : '') . "{$offset})",
                'offset' => $offset
            ];
        }

        ArrayHelper::multisort($timeZones, 'offset', SORT_DESC, SORT_NUMERIC);

        return $timeZones;
    }

    /**
     * @return array|BaseProfile|null|\yii\db\ActiveRecord
     */
    public function getUserAvatar()
    {
        return BaseProfile::find()
            ->select(['avatar'])
            ->where(['user_id' => Yii::$app->user->identity->getId()])
            ->asArray()
            ->one();
    }

    /**
     * @return array|Flow[]
     */
    public static function getWmLandings()
    {
        return Flow::find()
            ->select([
                'url'
            ])
            ->join('LEFT JOIN', 'flow_landing', 'flow_landing.flow_id = flow.flow_id')
            ->join('LEFT JOIN', 'landing', 'landing.landing_id = flow_landing.landing_id')
            ->where(['flow.wm_id' => Yii::$app->user->identity->getId()])
            ->asArray()
            ->all();
    }
}