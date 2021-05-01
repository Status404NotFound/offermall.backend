<?php

namespace crm\services\targets;

use common\helpers\FishHelper;
use common\models\offer\targets\wm\TargetWm;
use common\models\offer\targets\wm\TargetWmException;
use common\models\offer\targets\wm\TargetWmGroup;
use common\models\offer\targets\wm\TargetWmGroupRules;
use common\models\offer\targets\wm\TargetWmGroupRulesView;
use common\models\offer\targets\wm\TargetWmView;
use common\models\offer\targets\wm\WmOfferTarget;
use common\services\offer\WmTargetCommonService;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class WmTargetService
 * @package crm\services\targets
 */
class WmTargetService extends WmTargetCommonService
{
    public function getResponse($offer_id)
    {
        $offer_targets = $this->getStatuses($offer_id);
        foreach ($offer_targets as &$target) {
            $target['geo'] = $this->getStatusGeos($offer_id, $target['wm_offer_target_status'], $target['advert_offer_target_status']);
            foreach ($target['geo'] as &$geo) {
                $geo['wm'] = $this->getWmGroup($geo['wm_offer_target_id']);
                foreach ($geo['wm'] as &$group) {
                    $group_webmasters = $this->getGroupWebmasters($group['target_wm_group_id']);
                    $group['wm_id'] = [];
                    foreach ($group_webmasters as $wm) {
                        $group['wm_id'][] = $wm['wm_id'];
                    }
                    $group['target_wm_commission_rules'] = $this->getWmGroupRules($group['target_wm_group_id']);
                }
            }
        }
        return $offer_targets;
    }

    public function getStatuses($offer_id)
    {
        return WmOfferTarget::find()
            ->select('advert_offer_target_status, wm_offer_target_status')
            ->where(['offer_id' => $offer_id, 'active' => 1])
            ->groupBy('advert_offer_target_status, wm_offer_target_status')
            ->asArray()
            ->all();
    }

    public function getStatusGeos($offer_id, $wm_offer_target_status, $advert_offer_target_status)
    {
        return TargetWmView::find()
            ->select(['wm_offer_target_id', 'geo_id', 'geo_name', 'wot_active as active', 'view_for_all'])
            ->where([
                'offer_id' => $offer_id,
                'wm_offer_target_status' => $wm_offer_target_status,
                'advert_offer_target_status' => $advert_offer_target_status
            ])
            ->groupBy('geo_id, active, view_for_all, wm_offer_target_id')
            ->asArray()
            ->all();
    }

    public function getWmGroup($wm_offer_target_id)
    {
        $wm_group = TargetWmGroup::find()
            ->select(['target_wm_group_id', 'hold', 'active', 'base_commission', 'exceeded_commission', 'use_commission_rules'])
            ->where(['wm_offer_target_id' => $wm_offer_target_id])
            ->asArray()
            ->all();
        return $wm_group;
    }

    public function getGroupWebmasters($target_wm_group_id)
    {
        $group_webmasters = TargetWm::find()
            ->select('wm_id')
            ->where(['target_wm_group_id' => $target_wm_group_id, 'active' => 1])
            ->asArray()
            ->all();
        return $group_webmasters;
    }

    public function getWmGroupRules($target_wm_group_id)
    {
        $wm_group_rules = TargetWmGroupRules::find()
            ->select(['amount', 'commission'])
            ->where(['target_wm_group_id' => $target_wm_group_id])
            ->asArray()
            ->all();
        return $wm_group_rules;
    }
}