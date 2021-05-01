<?php

namespace common\services\webmaster;

use Yii;
use common\models\order\OrderStatus;
use common\models\offer\targets\wm\TargetWm;
use common\models\offer\targets\wm\TargetWmView;
use common\models\offer\targets\wm\TargetWmGroup;
use common\models\offer\targets\wm\TargetWmGroupRules;
use common\models\flow\Flow;
use yii\helpers\ArrayHelper;

/**
 * Class Helper
 * @package common\services\webmaster
 */
class Helper
{
    const approved_statuses = "(" .
    OrderStatus::WAITING_DELIVERY . ", " .
    OrderStatus::DELIVERY_IN_PROGRESS . ", " .
    OrderStatus::CANCELED . ", " .
    OrderStatus::NOT_PAID . ")";

    public function targetsQuery()
    {
        $targets = TargetWmGroup::find()
            ->select([
                'WOT.offer_id',
                'WOT.advert_offer_target_status',
                "IF(WOT.wm_offer_target_status IN ". self::approved_statuses .", 40, WOT.wm_offer_target_status) as wm_offer_target_status",
//                'WOT.wm_offer_target_status',
                'TWG.target_wm_group_id',
                'TWG.wm_offer_target_id',
                'TWG.base_commission',
                'TWG.view_for_all',
                'TWG.hold',
                'TW.target_wm_id',
                'TW.excepted',
                'TW.active',
                'TW.wm_id',
                'G.geo_name',
                'G.geo_id',
                'G.iso'
            ])
            ->from('target_wm_group TWG')
            ->leftJoin('wm_offer_target WOT', 'WOT.wm_offer_target_id = TWG.wm_offer_target_id')
            ->leftJoin('target_wm TW', 'TW.target_wm_group_id = TWG.target_wm_group_id')
            ->leftJoin('geo G', 'WOT.geo_id = G.geo_id');

        return $targets;
    }

    /**
     * @param $offer_id
     * @return array|\common\models\landing\Landing[]|TargetWmGroup[]|TargetWmGroupRules[]|\yii\db\ActiveRecord[]
     */
    public function getOfferTargets($offer_id)
    {
        $group_excepted = self::getWmExcepted();

        $targets = $this->targetsQuery();

        $targets->where([
            'WOT.offer_id' => $offer_id,
            'TW.wm_id' => Yii::$app->user->identity->getId(),
            'TW.excepted' => 0,
            'TWG.active' => 1,
        ])
            ->orWhere(
                ['and', ['<>', 'TW.wm_id', Yii::$app->user->identity->getId()],
                    ['WOT.offer_id' => $offer_id, 'TW.excepted' => 1, 'TWG.active' => 1],]
            )
            ->orWhere(
                ['and', ['is', 'TW.wm_id', null],
                    ['WOT.offer_id' => $offer_id, 'TW.excepted' => 1, 'TWG.active' => 1],
                ]
            );

        if (!is_null($group_excepted)) {
            $targets->andFilterWhere(['not in', 'TWG.target_wm_group_id', $group_excepted]);
        }

        $query = $targets
            ->asArray()
            ->all();

        return $query;
    }

    /**
     * @param null $wm_id
     * @return mixed
     */
    public static function getWmExcepted($wm_id = null)
    {
        $uid = isset($wm_id) ? $wm_id : Yii::$app->user->identity->getId();

        $query = TargetWm::find()
            ->select([
                'target_wm_group_id'
            ])
            ->where([
                'wm_id' => $uid,
                'excepted' => 1
            ])
            ->asArray()
            ->one();

        return ArrayHelper::getValue($query, 'target_wm_group_id');
    }

    /**
     * @param $target_wm_id
     * @return array|bool
     */
    public function getWmRules($target_wm_id)
    {
        if (!$commission = TargetWM::find()
            ->where(['target_wm_id' => $target_wm_id])
            ->andWhere(['IS NOT', 'target_wm_id', null])
            ->one()
        ) return false;
        $rules = [
            'base_commission' => $commission->targetWmGroup->base_commission ?? null,
            'exceeded_commission' => $commission->targetWmGroup->exceeded_commission ?? null,
            'use_commission_rules' => $commission->targetWmGroup->use_commission_rules,
            'rules_by_pcs' => TargetWmGroupRules::getWmGroupRulesByGroupId($commission->target_wm_group_id),
        ];
        return $rules;
    }

    /**
     * @param $data
     * @return array|string
     */
    public static function hideFields($data)
    {
        $percentage_of_view = 25;
        $fields = [];
        foreach ($data as $name => $field) {
            $count = strlen($field);
            $percent = round($count * $percentage_of_view / 100);
            $start = mb_substr(($field), 0, $percent);
            $end = mb_substr($field, $count - $percent, $count);
            $left = $count - $percent * 2;
            $fields = $start . str_repeat('*', $left) . $end;
        }

        return $fields;
    }

    /**
     * @param int $length
     * @param bool $numbersOnly
     * @param bool $capitals
     * @return string
     */
    public static function randomString($length = 8, $numbersOnly = false, $capitals = false)
    {
        $digits = '0123456789';
        $letters = "abcdefghijklmnopqrstuvwxyz" . $digits;
        $caps = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $alphabet = $numbersOnly ? $digits : ($capitals ? $letters . $caps : $letters);

        $pass = [];
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    /**
     * @return string
     */
    public static function generateFlowKey()
    {
        $randomKey = self::randomString(8, false, true);

        if (self::_isKeyExists($randomKey)) {
            return self::generateFlowKey();
        } else {
            return $randomKey;
        }
    }

    /**
     * @param $key
     * @return bool
     */
    private function _isKeyExists($key)
    {
        return Flow::find()->where(['flow_key' => $key])->exists();
    }

    /**
     * @param $query
     * @return array
     */
    public function concatenateUrl($query)
    {
        $new = [];
        foreach ($query as $domain) {

            if ($domain['url']{strlen($domain['url']) - 1} == '/') {
                $url = substr($domain['url'], 0, -1) . '/' . $domain['flow_key'] . '/?';
            } else {
                $url = $domain['url'] . '/' . $domain['flow_key'] . '/?';
            }

            if ($domain['sub_id_1']) {
                $url = $url . 'sub_id_1=' . $domain['sub_id_1'];
            }
            if ($domain['sub_id_2'] && $domain['sub_id_1']) {
                $url = $url . '&sub_id_2=' . $domain['sub_id_2'];
            } elseif ($domain['sub_id_2'] && !$domain['sub_id_1']) $url = $url . 'sub_id_2=' . $domain['sub_id_2'];

            if ($domain['sub_id_3'] && ($domain['sub_id_2'] || $domain['sub_id_1'])) {
                $url = $url . '&sub_id_3=' . $domain['sub_id_3'];
            } elseif ($domain['sub_id_3'] && !$domain['sub_id_2'] && !$domain['sub_id_1']) $url = $url . 'sub_id_3=' . $domain['sub_id_3'];

            if ($domain['sub_id_4'] && ($domain['sub_id_3'] || $domain['sub_id_2'] || $domain['sub_id_1'])) {
                $url = $url . '&sub_id_4=' . $domain['sub_id_4'];
            } elseif ($domain['sub_id_4'] && !$domain['sub_id_3'] && !$domain['sub_id_2'] && !$domain['sub_id_1']) $url = $url . 'sub_id_4=' . $domain['sub_id_4'];

            if ($domain['sub_id_5'] && ($domain['sub_id_4'] || $domain['sub_id_3'] || $domain['sub_id_2'] || $domain['sub_id_1'])) {
                $url = $url . '&sub_id_5=' . $domain['sub_id_5'];
            } elseif ($domain['sub_id_5'] && !$domain['sub_id_4'] && !$domain['sub_id_3'] && !$domain['sub_id_2'] && !$domain['sub_id_1']) $url = $url . 'sub_id_5=' . $domain['sub_id_5'];

            $new[] = [
                'flow_id' => $domain['flow_id'],
                'flow_name' => $domain['flow_name'],
                'flow_key' => $domain['flow_key'],
                'offer_name' => $domain['offer_name'],
                'created_at' => $domain['created_at'],
                'url' => $url,
            ];
        }

        return $new;
    }

    /**
     * @param $wm_id
     * @param $offer_id
     * @return bool
     */
    public static function checkWmAvailabilityTarget($wm_id, $offer_id)
    {
        return TargetWmView::find()->where(['wm_id' => $wm_id])->andWhere(['offer_id' => $offer_id])->exists();
    }
}