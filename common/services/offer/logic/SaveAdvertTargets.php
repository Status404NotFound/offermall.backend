<?php

namespace common\services\offer\logic;

use common\helpers\FishHelper;
use common\models\offer\Offer;
use common\models\offer\targets\advert\AdvertOfferTarget;
use common\models\offer\targets\advert\TargetAdvert;
use common\models\offer\targets\advert\TargetAdvertDailyRest;
use common\models\offer\targets\advert\TargetAdvertGroup;
use common\models\offer\targets\advert\TargetAdvertGroupRules;
use common\services\offer\exceptions\AdvertServiceException;
use common\services\offer\OfferNotFoundException;
use common\services\ValidateException;
use yii\base\InvalidParamException;
use yii\base\Exception;
use Yii;

class SaveAdvertTargets
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
        // TODO: Global warnings class
        $result = true;
        $advertOfferTargets = $this->findAdvertOfferTargets();
        foreach ($this->targets as $target) {
            $mainTx = Yii::$app->db->beginTransaction();
            try {
                $advert_offer_target_id = $this->saveAdvertOfferTargets(
                    $target['advert_offer_target_id'],
                    $target['advert_offer_target_status'],
                    $target['geo_id'],
                    $target['active'],
                    $target['wm_visible'],
                    $target['advert']
                );
                $mainTx->commit();
                unset($advertOfferTargets[$advert_offer_target_id]);
            } catch (InvalidParamException $e) {
                $this->errors['InvalidParamException'] = $e;
                $mainTx->rollBack();
                $result = false;
            } catch (ValidateException $e) {
                $this->errors['Exception'] = $e->getMessages();
                $mainTx->rollBack();
                $result = false;
            } catch (Exception $e) {
                $this->errors['Exception'] = $e;
                $mainTx->rollBack();
                $result = false;
            }
        }
        if ($result === true) {
            foreach ($advertOfferTargets as $advertOfferTarget) {
                $advertOfferTarget->delete();
            }
        }
        return $result;
    }

    private function saveAdvertOfferTargets($advert_offer_target_id, $advert_offer_target_status, $geo_id, $active, $wm_visible, $advert_group = [])
    {
        $advertOfferTarget = $this->findAdvertOfferTarget($advert_offer_target_id, $advert_offer_target_status, $geo_id) ?? new AdvertOfferTarget();
        $advertOfferTarget->setAttributes([
            'offer_id' => $this->offer_id,
            'advert_offer_target_status' => $advert_offer_target_status,
            'geo_id' => $geo_id,
            'active' => $active,
            'wm_visible' => $wm_visible
        ]);
        if (!$advertOfferTarget->save()) {
            throw new ValidateException($advertOfferTarget->errors);
        }
        if (!$this->saveTargetAdvertGroup($advert_group, $advertOfferTarget->advert_offer_target_id)) {
            throw new AdvertServiceException('Failed to save advert target settings');
        }
        return $advertOfferTarget->advert_offer_target_id;
    }

    public function saveTargetAdvertGroup($advert_group, $advert_offer_target_id)
    {
        $result = true;
        $targetAdvertGroups = TargetAdvertGroup::find()
            ->where(['advert_offer_target_id' => $advert_offer_target_id])
            ->indexBy('target_advert_group_id')
            ->all();
        foreach ($advert_group as $group) {
            if ($group['use_commission_rules'] == true && $group['exceeded_commission'] == false) {
                throw new AdvertServiceException('Failed to save advert target settings. NO exceeded commission.');
            }
            if (!isset($group['target_advert_group_id']))
                $group['target_advert_group_id'] = null;
            if (!isset($group['base_commission']))
                $group['base_commission'] = null;
            if (!isset($group['exceeded_commission']))
                $group['exceeded_commission'] = null;
            $targetAdvertGroup = $this->findTargetAdvertGroup($group['target_advert_group_id']) ?? new TargetAdvertGroup();
            $targetAdvertGroup->setAttributes([
                'advert_offer_target_id' => (integer)$advert_offer_target_id,
                'daily_limit' => (integer)$group['daily_limit'],
                'currency_id' => (integer)$group['currency_id'],
                'base_commission' => (float)$group['base_commission'],
                'exceeded_commission' => (float)$group['exceeded_commission'],
                'use_commission_rules' => (integer)$group['use_commission_rules'],
                'active' => (integer)$group['active'],
                'pay_online' => (integer)$group['pay_online'],
                'send_to_lp_crm' => (integer)$group['send_to_lp_crm'],
                'auto_send_to_lp_crm' => (integer)$group['auto_send_to_lp_crm'],
            ]);
            if (!$targetAdvertGroup->save()) {
                throw new ValidateException($targetAdvertGroup->errors);
            }
            if (!$this->saveTargetAdverts(
                $group['advert_group'],
                (integer)$group['daily_limit'],
                $targetAdvertGroup->target_advert_group_id,
                $group['active'],
                $group['pay_online'])
            ) {
                $result = false;
                throw new AdvertServiceException('Failed to save advert target settings');
            }
            if ($group['use_commission_rules'] == 1 && !$this->saveGroupRules($targetAdvertGroup->target_advert_group_id, $group['target_advert_group_rules'])) {
                $result = false;
                throw new AdvertServiceException('Failed to save advert commission rules');
            }
            unset($targetAdvertGroups[$targetAdvertGroup->target_advert_group_id]);
        }
        if ($result === true) {
            foreach ($targetAdvertGroups as $group) {
                $group->delete();
            }
        }
        return true;
    }


//    public function saveTargetAdverts22222($advert_group = [], $daily_limit, $target_advert_group_id)
//    {
//        if (empty($advert_group)) {
//            $this->errors['TargetAdvert'] = 'Adverts exists';
//            return false;
//        }
//        $result = true;
//        $group_adverts = TargetAdvert::find()->where(['target_advert_group_id' => $target_advert_group_id])
//            ->indexBy('advert_id')->all();
//        foreach ($advert_group as $advert_id) {
////            $targetAdvert =  $this->findTargetAdvert($target_advert_group_id, $advert_id)?? new TargetAdvert();
//
//            if (!$targetAdvert = $this->findTargetAdvert($target_advert_group_id, $advert_id)) {
//                $targetAdvert = new TargetAdvert();
//                $targetAdvert->setAttributes([
//                    'target_advert_group_id' => $target_advert_group_id,
//                    'advert_id' => intval($advert_id),
//                ]);
//            }
//
//            if (!$targetAdvert->save()) {
//                $this->errors['TargetAdvert'] = $targetAdvert->errors;
//                return false;
//            }
//            if (!$this->saveTargetAdvertDailyRest($daily_limit, $targetAdvert->target_advert_id)) {
//                throw new AdvertServiceException('Failed to save advert target settings');
//            }
//            unset($group_adverts[$targetAdvert->advert_id]);
//        }
//        if (!$this->saveInactiveGroupAdverts($advert_group, $target_advert_group_id))
//            throw new AdvertServiceException('Failed to remove adverts from group');
//        if ($result === true) {
//            foreach ($group_adverts as $advert) {
//                $advert->delete();
//            }
//        }
//        return true;
//    }


    public function saveTargetAdverts($advert_group = [], $daily_limit, $target_advert_group_id, $active, $pay_online)
    {
        if (empty($advert_group)) {
            throw new Exception('Adverts exists');
        }
        $result = true;
        $group_adverts = TargetAdvert::find()->where(['target_advert_group_id' => $target_advert_group_id])
            ->indexBy('advert_id')->all();
        foreach ($advert_group as $advert_id) {
            $targetAdvert = $this->findTargetAdvert($target_advert_group_id, $advert_id) ?? new TargetAdvert();
//            FishHelper::debug($targetAdvert, 1, 0);
            $targetAdvert->setAttributes([
                'target_advert_group_id' => $target_advert_group_id,
                'advert_id' => (integer)$advert_id,
                'active' => (integer)$active,
                'pay_online' => (integer)$pay_online,
            ]);
            if (!$targetAdvert->save()) {
                throw new ValidateException($targetAdvert->errors);
            }
            if (!$this->saveTargetAdvertDailyRest($daily_limit, $targetAdvert->target_advert_id))
                throw new AdvertServiceException('Failed to save advert target settings');
            unset($group_adverts[$targetAdvert->advert_id]);
        }
        if (!$this->saveInactiveGroupAdverts($advert_group, $target_advert_group_id))
            throw new AdvertServiceException('Failed to remove adverts from group');

        if ($result === true) {
            foreach ($group_adverts as $advert) {
                $advert->delete();
            }
        }
        return true;
    }

    public function saveTargetAdvertDailyRest($daily_limit, $target_advert_id)
    {
        $targetAdvertDailyRest = TargetAdvertDailyRest::findOne(['target_advert_id' => $target_advert_id]) ?? new TargetAdvertDailyRest();
        $targetAdvertDailyRest->target_advert_id = $target_advert_id;
        $targetAdvertDailyRest->rest = $daily_limit;
        if (!$targetAdvertDailyRest->save()) {
            throw new ValidateException($targetAdvertDailyRest->errors);
        }
        return true;
    }

    public function saveInactiveGroupAdverts($advert_group = [], $target_advert_group_id)
    {
        $group_adverts = TargetAdvert::find()
            ->where(['target_advert_group_id' => $target_advert_group_id])
            ->andWhere(['not in', 'advert_id', $advert_group])
            ->all();
        foreach ($group_adverts as $group_advert) {
            $group_advert->setAttribute('active', 0);
            if (!$group_advert->save()) {
                throw new ValidateException($group_advert->errors);
            }
        }
        return true;
    }

    private function saveGroupRules($target_advert_group_id, $commission_rules = [])
    {
        if (empty($commission_rules)) {
            throw new Exception('Commission rules exists');
        }
        foreach ($commission_rules as $rule) {
            $targetAdvertGroupRule = $this->findTargetGroupRule($target_advert_group_id, $rule['amount']) ?? new TargetAdvertGroupRules();
            $targetAdvertGroupRule->setAttributes([
                'target_advert_group_id' => $target_advert_group_id,
                'amount' => $rule['amount'],
                'commission' => $rule['commission']
            ]);
            if (!$targetAdvertGroupRule->save()) {
                throw new ValidateException($targetAdvertGroupRule->errors);
            }
        }
        return true;
    }

    public function findTargetAdvertGroup($target_advert_group_id)
    {
        return TargetAdvertGroup::findOne(['target_advert_group_id' => $target_advert_group_id]);

    }

    public function findTargetAdvert($target_advert_group_id, $advert_id)
    {
        return TargetAdvert::findOne([
            'target_advert_group_id' => $target_advert_group_id,
            'advert_id' => (integer)$advert_id,
        ]);
    }

    public function findTargetGroupRule($target_advert_group_id, $amount)
    {
        return TargetAdvertGroupRules::findOne([
            'target_advert_group_id' => $target_advert_group_id,
            'amount' => $amount
        ]);
    }

    public function findAdvertOfferTarget($advert_offer_target_id, $advert_offer_target_status, $geo_id)
    {
        return AdvertOfferTarget::findOne([
            'offer_id' => $this->offer_id,
            'advert_offer_target_id' => $advert_offer_target_id,
            'advert_offer_target_status' => $advert_offer_target_status,
            'geo_id' => $geo_id,
        ]);
    }

    public function findAdvertOfferTargets()
    {
        return AdvertOfferTarget::find()
            ->where(['offer_id' => $this->offer_id])
            ->indexBy('advert_offer_target_id')
            ->all();
    }

    private function isOfferExists()
    {
        return Offer::find()
            ->where(['offer_id' => $this->offer_id])
            ->count();
    }

    public function getErrors()
    {
        return $this->errors;
    }
}

//    public $request = [
//        'offer_id' => 1,
//        'targets' => [
//            [
//                'status' => 4,
//                'geo_id' => 1,
//                'advert_offer_target_id' => 123, // or null
//                'active' => 1, // or 0
//                'wm_visible' => 1, // or 0
//                'advert' => [
//                    [
//                        'advert_group' => [1, 2, 3, 4],
//                        'target_advert_group_id' => 121, // or NULL
//                        'active' => 0,
//                        'daily_limit' => 123,
//                        'currency_id' => '2',
//                        'land_price' => 100,
//                        'exceeded_commission' => 20,
//                        'base_commission' => 20,
//                        'use_commission_rules' => 1,
//                        'target_advert_group_rules' => [
//                            [
//                                'amount' => 1,
//                                'commission' => 12
//                            ],
//                            [
//                                'amount' => 2,
//                                'commission' => 11
//                            ]
//                        ]
//                    ],
//                    [
//                        'advert_group' => [5, 6, 7, 8],
//                        'target_advert_group_id' => 122, // or NULL
//                        'active' => 0,
//                        'daily_limit' => 123,
//                        'currency_id' => '2',
//                        'land_price' => 100,
//                        'exceeded_commission' => 20,
//                        'base_commission' => 20,
//                        'use_commission_rules' => 1,
//                        'target_advert_group_rules' => [
//                            [
//                                'amount' => 1,
//                                'commission' => 12
//                            ],
//                            [
//                                'amount' => 2,
//                                'commission' => 11
//                            ]
//                        ]
//                    ],
//                ]
//            ],
//            [
//                'status' => 4,
//                'geo_id' => 1,
//                'active' => 1, // or 0
//                'wm_visible' => 1, // or 0
//                'advert' => [
//                    [
//                        'advert_group' => [1, 2, 3, 4],
//                        'target_advert_group_id' => 122, // or NULL
//                        'active' => 0,
//                        'daily_limit' => 123,
//                        'currency_id' => '2',
//                        'land_price' => 100,
//                        'exceeded_commission' => 20,
//                        'base_commission' => 20,
//                        'use_commission_rules' => 1,
//                        'target_advert_group_rules' => [
//                            [
//                                'amount' => 1,
//                                'commission' => 12
//                            ],
//                            [
//                                'amount' => 2,
//                                'commission' => 11
//                            ]
//                        ]
//                    ],
//                    [
//                        'advert_group' => [5, 6, 7, 8],
//                        'target_advert_group_id' => 127, // or NULL
//                        'active' => 0,
//                        'daily_limit' => 123,
//                        'currency_id' => '2',
//                        'land_price' => 100,
//                        'exceeded_commission' => 20,
//                        'base_commission' => 20,
//                        'use_commission_rules' => 1,
//                        'target_advert_group_rules' => [
//                            [
//                                'amount' => 1,
//                                'commission' => 12
//                            ],
//                            [
//                                'amount' => 2,
//                                'commission' => 11
//                            ]
//                        ]
//                    ],
//                ]
//            ],
//        ]
//    ];
