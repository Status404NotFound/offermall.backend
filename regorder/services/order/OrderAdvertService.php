<?php

namespace regorder\services\order;

use common\helpers\FishHelper;
use common\models\offer\targets\advert\AdvertOfferTarget;
use common\models\offer\targets\advert\TargetAdvert;
use common\models\offer\targets\advert\TargetAdvertDailyRest;
use common\models\offer\targets\advert\TargetAdvertGroup;
use common\models\order\Order;
use common\models\order\OrderData;
use regorder\services\order\exceptions\OrderAdvertServiceException;
use yii\base\Exception;
use yii\web\NotFoundHttpException;

/**
 * Class OrderTargetService
 * @package regorder\services\order
 */
class OrderAdvertService
{
    /**
     * @var Order
     */
    private $order;
    private $geo_id;
    private $advert_offer_target_status = null;

    /**
     * OrderAdvertService constructor.
     * @param Order $order
     * @param $geo_id
     * @param $advert_offer_target_status
     */
    public function __construct(Order $order, $geo_id, $advert_offer_target_status = null)
    {
        $this->order = $order;
        $this->geo_id = $geo_id;
        $this->advert_offer_target_status = $advert_offer_target_status;
    }

    /**
     * getting Order TargetAdvertId and setting OrderData OwnerId
     * @return mixed
     * @throws Exception
     */
    public function getTargetAdvertId()
    {
        if (!$target_advert_groups = $this->getTargetAdvertGroups())
//            throw new OrderAdvertServiceException('TargetAdvertGroups Not Found.');
            return null;
        if (!$advertDailyRest = $this->getAdvertDailyRest($target_advert_groups))
//            throw new OrderAdvertServiceException('Can not change daily limit.');
            return null;
        if (!$this->removeAdvertRest($advertDailyRest)) {
            //            throw new OrderAdvertServiceException('Failed to change Advert rests.');
            // TODO: Log it;
            return null;
        }
        return $advertDailyRest->targetAdvert->target_advert_id;
    }

    /**
     * @param $target_advert_groups
     * @return TargetAdvertDailyRest|null
     */
    private function getAdvertDailyRest($target_advert_groups)
    {
        $with_rest_adverts = [];
        $no_rest_adverts = [];
        foreach ($target_advert_groups as $group) {
            /** @var TargetAdvertGroup $group */
            if ($group->active == 0) continue;
            foreach ($group->targetAdverts as $advert) {
                if ($advert->active == 0) continue;
                if ($advert->targetAdvertDailyRest->rest <= 0) {
                    $no_rest_adverts[$advert->target_advert_id] = $advert->targetAdvertDailyRest;
                } else {
                    $with_rest_adverts[$advert->target_advert_id] = $advert->targetAdvertDailyRest;
                }
            }
        }
        $time_now = strtotime('now');
        $oldest_updated_rest = null;
        $maxLimitGroup = new TargetAdvertGroup();
        $unlim_team = [];
        if (empty($with_rest_adverts)) {
            if (!empty($no_rest_adverts)) {
                foreach ($no_rest_adverts as $advert_rest) {
                    /** @var TargetAdvertDailyRest $advert_rest */
                    $daily_limit = $advert_rest->targetAdvert->targetAdvertGroup->daily_limit;
                    if ($daily_limit > $maxLimitGroup->daily_limit) $maxLimitGroup = $advert_rest->targetAdvert->targetAdvertGroup;
                    if ($daily_limit == 0)
                        $unlim_team = array_merge($unlim_team, $advert_rest->targetAdvert->targetAdvertGroup->targetAdverts);
                }
                if (!empty($unlim_team)) {
                    $foreachGroup = $unlim_team;
                } elseif (!empty($maxLimitGroup)) {
                    $foreachGroup = $maxLimitGroup;
                } else {
                    // TODO: Log it or find how to reg it.
                    return null;
                }

                foreach ($foreachGroup as $targetAdvert) {
                    /** @var TargetAdvert $targetAdvert */
                    if ($time_now > strtotime($targetAdvert->targetAdvertDailyRest->date)) {
                        $time_now = strtotime($targetAdvert->targetAdvertDailyRest->date);
                        $oldest_updated_rest = clone $targetAdvert->targetAdvertDailyRest;
                    }
                }
            } else {
                // TODO: Log it or find how to reg it.
                return null;
            }
        } else {
            foreach ($with_rest_adverts as $advert_rest) {
                /** @var TargetAdvertDailyRest $advert_rest */
                if ($time_now > strtotime($advert_rest->date)) {
                    $time_now = strtotime($advert_rest->date);
                    $oldest_updated_rest = clone $advert_rest;
                }
            }

        }
        return $oldest_updated_rest;
    }

    /**
     * @param $advertDailyRest
     * @return bool
     */
    private function removeAdvertRest(TargetAdvertDailyRest $advertDailyRest)
    {
        $advertDailyRest->rest--;
        return $advertDailyRest->save();
    }

    /**
     * @return array|TargetAdvertGroup[]|null
     */
    private function getTargetAdvertGroups()
    {
        if ($this->advert_offer_target_status) {
            $advert_offer_targets = AdvertOfferTarget::findAll([
                'offer_id' => $this->order->offer_id,
                'geo_id' => $this->geo_id,
                'advert_offer_target_status' => $this->advert_offer_target_status,
            ]);
        } else {
            $advert_offer_targets = AdvertOfferTarget::findAll([
                'offer_id' => $this->order->offer_id,
                'geo_id' => $this->geo_id,
            ]);
        }
        if (!isset($advert_offer_targets)) {
            $advert_offer_targets = AdvertOfferTarget::findAll(['offer_id' => $this->order->offer_id]);
        }
        if (isset($advert_offer_targets)) {
            $target_advert_groups = [];
            foreach ($advert_offer_targets as $offer_target) {
                $target_advert_groups = array_merge($target_advert_groups, $offer_target->targetAdvertGroups);
            }
        }
        if (!isset($target_advert_groups) || empty($target_advert_groups)) {
            if (isset($this->order->targetWm)) {
                $target_advert_groups = $this->order->targetWm->targetWmGroup->wmOfferTarget->advertOfferTarget->targetAdvertGroups;
            } else {
                // TODO: Log it or find how to reg it.
                $target_advert_groups = null;
            }
        }
        return $target_advert_groups;
    }
}
