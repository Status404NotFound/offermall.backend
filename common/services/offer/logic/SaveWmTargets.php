<?php

namespace common\services\offer\logic;

use common\helpers\FishHelper;
use common\models\offer\Offer;
use common\models\offer\targets\advert\AdvertOfferTarget;
use common\models\offer\targets\wm\TargetWm;
use common\models\offer\targets\wm\TargetWmGroup;
use common\models\offer\targets\wm\TargetWmGroupRules;
use common\models\offer\targets\wm\WmOfferTarget;
use common\services\offer\exceptions\WmServiceException;
use common\services\offer\OfferNotFoundException;
use common\services\ValidateException;
use yii\base\InvalidParamException;
use yii\base\Exception;
use Yii;

class SaveWmTargets
{
    public $errors = [];
    public $offer_id;
    public $targets;

    public function __construct($offer_id, $targets)
    {
        $this->offer_id = $offer_id;
        $this->targets = $targets;
    }

    public function execute()
    {
        if (!$this->isOfferExists()) {
            throw new OfferNotFoundException('Offer not found');
        }
        return $this->saveData();
    }

    private function saveData()
    {
        $wmOfferTargets = WmOfferTarget::find()
            ->where(['offer_id' => $this->offer_id])
            ->indexBy('wm_offer_target_id')
            ->all();
        foreach ($this->targets as $wm_target) {
            $mainTx = Yii::$app->db->beginTransaction();
            try {
                if (!$wm_offer_target_id = $this->saveWmOfferTargets($wm_target['advert_offer_target_status'],
                    $wm_target['wm_offer_target_status'],
                    $wm_target['geo_id'],
                    $wm_target['active'],
                    $wm_target['wm'],
                    $wm_target['view_for_all'])
                )
                    throw new WmServiceException('Failed to save wm offer target');
                $mainTx->commit();
                unset($wmOfferTargets[$wm_offer_target_id]);
            } catch (InvalidParamException $e) {
                $this->errors['InvalidParamException'] = $e;
                $mainTx->rollBack();
            } catch (Exception $e) {
                $this->errors['Exception'] = $e;
                $mainTx->rollBack();
            }
        }
        foreach ($wmOfferTargets as $wmOfferTarget) {
            $wmOfferTarget->delete();
        }

        return true;
    }

    public function saveWmOfferTargets($advert_offer_target_status, $wm_offer_target_status, $geo_id, $active, $wm_group, $view_for_all)
    {
        if (!$this->isAdvertOfferTargetExists($advert_offer_target_status, $geo_id)) {
            throw new WmServiceException('AdvertOfferTarget not found');
//            $this->errors['AdvertOfferTarget'] = 'AdvertOfferTarget not found';
//            return false;
        }
        $wmOfferTarget = $this->findWmOfferTarget($advert_offer_target_status, $wm_offer_target_status, $geo_id) ?? new WmOfferTarget();
        $wmOfferTarget->setAttributes([
            'offer_id' => $this->offer_id,
            'advert_offer_target_status' => $advert_offer_target_status,
            'wm_offer_target_status' => $wm_offer_target_status,
            'geo_id' => $geo_id,
            'active' => $active,
        ]);
        if (!$wmOfferTarget->save()) {
            throw new ValidateException($wmOfferTarget->errors);
//            $this->errors['wmOfferTarget'] = $wmOfferTarget->errors;
//            return false;
        }
        if (!$this->saveTargetWmGroup($wm_group, $wmOfferTarget->wm_offer_target_id, $view_for_all)) {
            throw new WmServiceException('Failed to save webmaster target settings');
        }
        return $wmOfferTarget->wm_offer_target_id;
    }

    public function saveTargetWmGroup($wm_group, $wm_offer_target_id, $view_for_all)
    {
        $targetWmGroups = TargetWmGroup::find()
            ->where(['wm_offer_target_id' => $wm_offer_target_id])
            ->indexBy('target_wm_group_id')
            ->all();
        foreach ($wm_group as $group) {
            if ($group['use_commission_rules'] == true && $group['exceeded_commission'] == false) {
                throw new WmServiceException('Failed to save webmaster target settings. NO exceeded commission.');
            }
            $targetWmGroup = $this->findTargetWmGroup($group['target_wm_group_id']) ?? new TargetWmGroup();
            $targetWmGroup->setAttributes([
                'wm_offer_target_id' => (integer)$wm_offer_target_id,
                'base_commission' => (float)$group['base_commission'],
                'exceeded_commission' => (integer)$group['exceeded_commission'],
                'hold' => (integer)$group['hold'],
                'active' => (integer)$group['active'],
                'use_commission_rules' => (integer)$group['use_commission_rules'],
                'view_for_all' => (integer)$view_for_all,
            ]);
            if (!$targetWmGroup->save()) {
                throw new WmServiceException($targetWmGroup->errors);
//                $this->errors['targetWmGroup'] = $targetWmGroup->errors;
//                return false;
            }
            if (!$this->saveTargetWm($group['wm_group'], $targetWmGroup->target_wm_group_id, $view_for_all)) {
                throw new WmServiceException('Failed to save webmaster target settings');
            }

            if ($group['use_commission_rules'] == 1 && !$this->saveGroupRules($targetWmGroup->target_wm_group_id, $group['target_wm_commission_rules'])) {
                throw new WmServiceException('Failed to save webmaster commission rules');
            }
            unset($targetWmGroups[$targetWmGroup->target_wm_group_id]);
        }
        foreach ($targetWmGroups as $group) {
            $group->delete();
//            $group->setAttribute('active', 0);
//            $group->save();
        }

        return true;
    }

    public function saveTargetWm($wm_group = [], $target_wm_group_id, $excepted)
    {
        // TODO: set NULL into wm_id field if view_for_all is TRUE and excepted wm are not set. For flow_target_wm.
        if (empty($wm_group) && $excepted == 0) {
            throw new WmServiceException('No Webmasters.');
        }
        $target_webmasters = TargetWm::find()->where(['target_wm_group_id' => $target_wm_group_id])
            ->indexBy('target_wm_id')->all();
        foreach ($wm_group as $wm_id) {
            $wm_id = ($wm_id == 'null') ? null : $wm_id;
            $targetWm = $this->findTargetWm($target_wm_group_id, $wm_id) ?? new TargetWm();
            $targetWm->setAttributes([
                'target_wm_group_id' => $target_wm_group_id,
                'wm_id' => $wm_id,
                'excepted' => $excepted,
            ]);
            if (!$targetWm->validate() || !$targetWm->save()) {
                $this->errors['targetWm'] = $targetWm->errors;
            }
            unset($target_webmasters[$targetWm->target_wm_id]);
        }
        if (!$this->saveInactiveGroupWm($wm_group, $target_wm_group_id)) {
            throw new WmServiceException('Failed to remove wm from group');
        }
        foreach ($target_webmasters as $wm) {
            $wm->delete();
//            $wm->setAttribute('active', 0);
//            $wm->save();
        }
        return true;
    }

    public function saveInactiveGroupWm($wm_group, $target_wm_group_id)
    {
        $group_adverts = TargetWm::find()
            ->where(['target_wm_group_id' => $target_wm_group_id])
            ->andWhere(['not in', 'wm_id', $wm_group])
//            ->asArray()
            ->all();
        foreach ($group_adverts as $group_wm) {
            $group_wm->setAttribute('active', 0);
            if (!$group_wm->save()) {
                $this->errors['TargetWm'] = $group_wm->errors;
                return false;
            }
        }
        return true;
    }

    public function saveGroupRules($target_wm_group_id, $commission_rules = [])
    {
        if (empty($commission_rules)) {
            $this->errors['targetWmGroupRules'] = 'Commission rules exists';
            return false;
        }
        foreach ($commission_rules as $rule) {
            $targetWmGroupRule = $this->findTargetGroupRule($target_wm_group_id, $rule['amount']) ?? new TargetWmGroupRules();
            $targetWmGroupRule->setAttributes([
                'target_wm_group_id' => $target_wm_group_id,
                'amount' => $rule['amount'],
                'commission' => $rule['commission']
            ]);
            if (!$targetWmGroupRule->validate() || !$targetWmGroupRule->save()) {
                $this->errors['targetWmGroupRules'] = $targetWmGroupRule->errors;
                return false;
            }
        }
        return true;
    }

    public function findWmOfferTarget($advert_offer_target_status, $wm_offer_target_status, $geo_id)
    {
        return WmOfferTarget::findOne([
            'offer_id' => $this->offer_id,
            'advert_offer_target_status' => $advert_offer_target_status,
            'wm_offer_target_status' => $wm_offer_target_status,
            'geo_id' => $geo_id,
        ]);
    }

    public function findTargetWmGroup($target_wm_group_id)
    {
        return TargetWmGroup::findOne(['target_wm_group_id' => $target_wm_group_id]);
    }

    public function findTargetWm($target_wm_group_id, $wm_id)
    {
        return TargetWm::findOne([
            'target_wm_group_id' => $target_wm_group_id,
            'wm_id' => $wm_id
        ]);
    }

    public function findTargetGroupRule($target_wm_group_id, $amount)
    {
        return TargetWmGroupRules::findOne([
            'target_wm_group_id' => $target_wm_group_id,
            'amount' => $amount
        ]);
    }

    private function isOfferExists()
    {
        return Offer::find()
            ->where(['offer_id' => $this->offer_id])
            ->count();
    }

    public function isAdvertOfferTargetExists($advert_offer_target_status, $geo_id)
    {
        return AdvertOfferTarget::find()->where([
            'offer_id' => $this->offer_id,
            'advert_offer_target_status' => $advert_offer_target_status,
            'geo_id' => $geo_id
        ])->count();
    }

    public function getErrors()
    {
        return $this->errors;
    }
}

//    public $request = [
//        "offer_id" => 1,
//        "targets" => [
//            [
//                "advert_offer_target_status" => 1,
//                'status' => 2,
//                'geo_id' => 1,
//                'active' => true,
////              if  'view_for_all' => false,
//                'wm' => [
//                    [
//                        'wm_id' => 4,
//                        'base_commission' => 200,
//                        'exceeded_commission' => 100,
//                        'hold' => 360,
//                        'active' => 1,
//                        'use_commission_rules' => 0,
//                        'target_wm_commission_rules' => [
//                            ['amount' => 1, 'commission' => 199.9],
//                            ['amount' => 2, 'commission' => 200.9],
//                            ['amount' => 3, 'commission' => 300.9],
//                        ],
//                    ],
//                    [
//                        'wm_id' => 4,
//                        'base_commission' => 200,
//                        'exceeded_commission' => null,
//                        'hold' => 360,
//                        'active' => 1,
//                        'use_commission_rules' => 0,
//                        'target_wm_commission_rules' => [
//                            ['amount' => 1, 'commission' => 199.9],
//                            ['amount' => 2, 'commission' => 200.9],
//                            ['amount' => 3, 'commission' => 300.9],
//                        ],
//                    ],
//                ],
//            ],
//            [
//                "advert_offer_target_status" => 1,
//                'status' => 2,
//                'geo_id' => 2,
//                'active' => true,
////              if  'view_for_all' => false,
//                'wm' => [
//                    [
//                        'wm_id' => 4,
//                        'base_commission' => 200,
//                        'exceeded_commission' => 100,
//                        'hold' => 360,
//                        'active' => 1,
//                        'use_commission_rules' => 0,
//                        'target_wm_commission_rules' => [
//                            ['amount' => 1, 'commission' => 199.9],
//                            ['amount' => 2, 'commission' => 200.9],
//                            ['amount' => 3, 'commission' => 300.9],
//                        ],
//                    ],
//                    [
//                        'wm_id' => 1,
//                        'base_commission' => 200,
//                        'exceeded_commission' => 70,
//                        'hold' => 360,
//                        'active' => 1,
//                        'use_commission_rules' => 1,
//                        'target_wm_commission_rules' => [
//                            ['amount' => 1, 'commission' => 190.9],
//                            ['amount' => 2, 'commission' => 199.9],
//                            ['amount' => 3, 'commission' => 299.9],
//                        ],
//                    ],
//                ],
//            ],
//            [
//                "advert_offer_target_status" => 2,
//                'status' => 3,
//                'geo_id' => 3,
//                'active' => true,
////              if  'view_for_all' => true,
//                'wm' => [
//                    [
//                        'wm_id' => [1, 2, 3], // .......
//                        'target_wm_group_id' => 'int / null',
//                        'base_commission' => 200,
//                        'exceeded_commission' => 70,
//                        'hold' => 360,
//                        'active' => 1,
//                        'use_commission_rules' => 1,
//                        'target_wm_commission_rules' => [
//                            ['amount' => 1, 'commission' => 190.9],
//                            ['amount' => 2, 'commission' => 199.9],
//                            ['amount' => 3, 'commission' => 299.9],
//                        ],
//                    ],
//                ],
//            ],
//        ],
//    ];
