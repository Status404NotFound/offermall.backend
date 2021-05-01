<?php

namespace common\services\offer;

use common\helpers\FishHelper;
use common\models\offer\Offer;
use common\models\offer\targets\advert\sku\TargetAdvertSku;
use common\models\offer\targets\advert\sku\TargetAdvertSkuRules;
use common\models\offer\targets\advert\TargetAdvert;
use common\services\ValidateException;
use crm\services\offer\OfferSkuServiceException;
use Yii;
use yii\base\InvalidParamException;
use yii\db\Exception;

class OfferSkuCommonService
{
    //  TODO: Replace into same logic class
    public $errors = [];

    public function getErrors()
    {
        return $this->errors;
    }

    public function create($offer_id, $offer_skus)
    {
        if (!$this->isOfferExists($offer_id)) {
            $this->errors['Offer'] = 'Offer not found';
            return false;
        }
        return $this->saveData($offer_skus);
    }

    public function saveData($offer_skus)
    {
        $result = true;
        foreach ($offer_skus as $offer_sku) {
            $mainTx = Yii::$app->db->beginTransaction();
            try {
                if (!$this->saveOfferSku(
                    $offer_sku['target_advert_id'],
                    $offer_sku['sku'])
                )
                    throw new OfferSkuServiceException('Failed to save target sku');
                $mainTx->commit();
            } catch (InvalidParamException $e) {
                $this->errors['InvalidParamException'] = $e;
                $mainTx->rollBack();
                $result = false;
            } catch (Exception $e) {
                $this->errors['Exception'] = $e;
                $mainTx->rollBack();
                $result = false;
            }
        }
        return ($result == true) ? $result : $this->getErrors();
    }

    public function saveOfferSku($target_advert_id, $skus)
    {
        $offerSkus = TargetAdvertSku::find()->where(['target_advert_id' => $target_advert_id])
            ->indexBy('target_advert_sku_id')->all();

        foreach ($skus as $sku) {

//            FishHelper::debug((integer)$sku['sku_id'] == null);

            $offerSku = $this->findOfferSku($sku['target_advert_sku_id']) ?? new TargetAdvertSku();
            $offerSku->setAttributes([
                'target_advert_id' => (integer)$target_advert_id,
                'sku_id' => ((integer)$sku['sku_id'] == NULL) ? NULL : (integer)$sku['sku_id'],
                'base_cost' => ($sku['base_cost'] === 'null') ? NULL : (float)$sku['base_cost'],
                'exceeded_cost' => ($sku['exceeded_cost'] == 'null') ? NULL : (float)$sku['exceeded_cost'],
                'is_upsale' => (integer)$sku['is_upsale'],
                'is_bookkeeping' => 1,
                'use_sku_cost_rules' => (integer)$sku['use_sku_cost_rules'],
                'use_extended_rules' => (integer)$sku['use_extended_rules'],
            ]);

//            if ($sku['sku_id'] !== 'null') {
//                $offerSku->sku_id = $sku['sku_id'];
//            }
//            else {
//                $offerSku->sku_id = 145;
//            }

            if (!$offerSku->save()) {
                FishHelper::debug($offerSku->errors);
//                throw new ValidateException($offerSku->errors);
            }
            if ($sku['use_sku_cost_rules'] == 1) {
                if (!$this->saveSkuRules($target_advert_id, $offerSku->target_advert_sku_id, $offerSku->sku_id, $sku['target_advert_sku_rules']))
                    throw new OfferSkuServiceException('Failed to save offer sku');
            }
            unset($offerSkus[$offerSku->target_advert_sku_id]);
        }
        foreach ($offerSkus as $offerSku) {
            if (!$offerSku->delete())
                throw new OfferSkuServiceException('Failed to save changes.');
        }
        return true;
    }

    public function saveSkuRules($target_advert_id, $target_advert_sku_id, $sku_id, $target_advert_sku_rules = null)
    {
        $offerSkuRules = TargetAdvertSkuRules::find()->where([
            'target_advert_sku_id' => $target_advert_sku_id,
            'target_advert_id' => $target_advert_id,
            'sku_id' => $sku_id
        ])->indexBy('target_advert_sku_rule_id')->all();

        if (!$target_advert_sku_rules) {
            throw new OfferSkuServiceException('Sku rules not sended');
        }
//        if ($target_advert_sku_id == 194) FishHelper::debug($target_advert_sku_rules);
        foreach ($target_advert_sku_rules as $rule) {
            $targetAdvertSkuRule = $this->findOfferSkuRule($target_advert_id, $sku_id, $rule['amount']) ?? new TargetAdvertSkuRules();

            if ($sku_id == 143 && $rule['amount'] == null) {
//                FishHelper::debug($rule['amount'] == null);
                $rule['amount'] = 1;
            }

            $targetAdvertSkuRule->setAttributes([
                'target_advert_id' => $target_advert_id,
                'target_advert_sku_id' => $target_advert_sku_id,
                'sku_id' => $sku_id,
                'amount' => $rule['amount'],
                'cost' => $rule['commission']
            ]);
            if (!$targetAdvertSkuRule->save()) {
                FishHelper::debug($targetAdvertSkuRule->errors);
                $this->errors['targetWmCommissionRules'] = $targetAdvertSkuRule->errors;
                return false;
            }
            unset($offerSkuRules[$targetAdvertSkuRule->target_advert_sku_rule_id]);
        }
        foreach ($offerSkuRules as $offerSkuRule) {
            if (!$offerSkuRule->delete())
                throw new OfferSkuServiceException('Failed to save changes');
        }
        return true;
    }

    public function findOfferSkuRule($target_advert_id, $sku_id, $amount)
    {
        return TargetAdvertSkuRules::findOne([
            'target_advert_id' => $target_advert_id,
            'sku_id' => $sku_id,
            'amount' => $amount
        ]);
    }

    public function findOfferSku($target_advert_sku_id)
    {
        return TargetAdvertSku::findOne(['target_advert_sku_id' => $target_advert_sku_id]);
    }

    private function isOfferExists($offer_id)
    {
        return Offer::find()
            ->where(['offer_id' => $offer_id])
            ->count();
    }

    //    public $request = [
//        'offer_id' => 1,
//        'offer_skus' => [
//            [
//                'advert_offer_target_id' => 2,
//                'status' => 3,
//                'geo_id' => 1,
//                'advert_id' => 4,
//                'skus' => [
//                    [
////                       if All SKU == true
//                        'sku_id' => null,
//                        'base_cost' => 200,
//                        'exceeded_cost' => 150, // or null,
//                        'is_upsale' => 1, // or 0,
//                        'use_extended_rules' => 1, // or 0,
//                        'use_sku_cost_rules' => 1, // or 0,
//                        'target_advert_sku_rules' => [
//                            ['amount' => 1, 'cost' => 190.9],
//                            ['amount' => 2, 'cost' => 199.9],
//                            ['amount' => 3, 'cost' => 299.9],
//                        ]
//                    ]
//                ]
//            ],
//            [
//                'advert_offer_target_id' => 1,
//                'status' => 5,
//                'geo_id' => 3,
//                'advert_id' => 3,
//                'skus' => [
//                    [
////                       if All SKU == false
//                        'sku_id' => 1,
//                        'base_cost' => 200,
//                        'exceeded_cost' => 150, // or null,
//                        'is_upsale' => 0, // or 0,
//                        'use_extended_rules' => 1, // or 0,
//                        'use_sku_cost_rules' => 0, // or 1,
//                        'target_advert_sku_rules' => [
//                            ['amount' => 1, 'cost' => 190.9],
//                            ['amount' => 2, 'cost' => 199.9],
//                            ['amount' => 3, 'cost' => 299.9],
//                        ]
//                    ],
//                    [
////                       if All SKU == false
//                        'sku_id' => 2,
//                        'base_cost' => 200,
//                        'exceeded_cost' => 150, // or null,
//                        'is_upsale' => 0, // or 0,
//                        'use_extended_rules' => 1, // or 0,
//                        'use_sku_cost_rules' => 1, // or 0,
//                        'target_advert_sku_rules' => [
//                            ['amount' => 1, 'cost' => 190.9],
//                            ['amount' => 2, 'cost' => 199.9],
//                            ['amount' => 3, 'cost' => 299.9],
//                        ]
//                    ],
//                ]
//            ],
//        ]
//    ];
}
