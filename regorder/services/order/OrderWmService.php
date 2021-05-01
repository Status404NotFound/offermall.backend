<?php

namespace regorder\services\order;

use common\helpers\FishHelper;
use common\models\offer\targets\wm\TargetWm;
use common\models\offer\targets\wm\TargetWmGroup;
use common\models\offer\targets\wm\WmOfferTarget;
use common\models\order\Order;
use common\modules\user\models\tables\User;
use regorder\services\order\exceptions\TargetWmNotFoundException;

/**
 * Class OrderWmService
 * @package regorder\services\order
 */
class OrderWmService
{
    /**
     * @var Order $order
     */
    private $order;
    private $geo_id;

    /**
     * OrderWmService constructor.
     * @param Order $order
     * @param $geo_id
     */
    public function __construct(Order $order, $geo_id)
    {
        $this->order = $order;
        $this->geo_id = $geo_id;
    }

    /**
     * @param null $advert_offer_target_status
     * @param null $wm_id
     * @return int|null
     */
    public function getTargetWmId($advert_offer_target_status = null, $wm_id = null)
    {
        if ($advert_offer_target_status) {
            $wmOfferTargets = WmOfferTarget::findAll([
                'offer_id' => $this->order->offer_id,
                'advert_offer_target_status' => $advert_offer_target_status,
                'geo_id' => $this->geo_id,
            ]);
        } else {
            $wmOfferTargets = WmOfferTarget::findAll([
                'offer_id' => $this->order->offer_id,
                'geo_id' => $this->geo_id,
            ]);
        }
        if (empty($wmOfferTargets)) {
            $wmOfferTargets = WmOfferTarget::findAll(['offer_id' => $this->order->offer_id]);
        }
        $wmGroups = [];
        foreach ($wmOfferTargets as $wmOfferTarget) {
            $wmGroups = array_merge($wmGroups, $wmOfferTarget->targetWmGroups);
        }
        foreach ($wmGroups as $group) {
            $groupWebmasters = $group->targetWms;
            foreach ($groupWebmasters as $wm) {
                /** @var TargetWm $wm */
                if (($wm->wm_id == $wm_id && $wm->excepted == 0) || ($wm->wm_id != $wm_id && $wm->excepted == 1)) {
                    $targetWmId = $wm->target_wm_id;
                }
            }
        }
        return isset($targetWmId) ? $targetWmId : null;
    }
}