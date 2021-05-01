<?php

namespace crm\services\offer;

use common\helpers\FishHelper;
use common\models\offer\Offer;
use common\models\offer\OfferSku;
use common\models\order\OfferStatus;
use common\models\offer\OfferView;
use common\services\offer\OfferCommonService;
use crm\services\offer\logic\OfferSearch;
use common\models\offer\targets\advert\TargetAdvertGroup;
use common\services\ValidateException;
use Yii;

/**
 * Class OfferService
 * @package crm\services\offer
 */
class OfferService extends OfferCommonService
{
    /**
     * @param OfferSku $offer_sku
     * @return bool
     */
    public function saveSku(OfferSku $offer_sku)
    {
        $offer_sku->setAttribute('created_by', Yii::$app->user->id);
        $offer_sku->setAttribute('updated_by', Yii::$app->user->id);
        if ($model = OfferSku::findOne(['sku_id' => $offer_sku->sku_id, 'offer_id' => $offer_sku->targetAdvert->targetAdvertGroup->advertOfferTarget->offer_id])) {
            return $this->updateSku($model);
        }
        $offer_sku->validate();

        // TODO: Catch errors and exceptions
        return $offer_sku->save();
    }

    /**
     * @param OfferSku $offer_sku
     * @return bool
     */
    public function updateSku(OfferSku $offer_sku)
    {
        return $offer_sku->save();
    }

    public function findOffers($filters = [], $pagination = null, $sort_field = null, $sortOrder = null)
    {
        $cmd = new OfferSearch();
        $result = $cmd->search($filters, $pagination, $sort_field, $sortOrder);
        return $result;
    }

    /**
     * @param $product_id
     * @return array
     */
    public function getSkuList($product_id)
    {
        $sku_model = new SkuModel();
        $skuList = $sku_model->getSkuList($product_id);

        // TODO: catch exceptions and errorrs
        return $skuList;
    }

    /**
     * @return array
     */
    public function offerStatusList()
    {
        $offerStatusList = (new OfferStatus)->attributeLabels();
        return $offerStatusList;
    }

    /**
     * @param OfferSkuRules $offer_sku_rules
     * @return bool
     */
    public function saveOfferSkuRules(OfferSkuRules $offer_sku_rules)
    {
        if ($model = OfferSkuRules::findOne([
            'sku_id' => $offer_sku_rules->sku_id,
            'offer_id' => $offer_sku_rules->offer_id,
            'count' => $offer_sku_rules->count,
        ])
        ) {
            if ($offer_sku_rules->cost != $model->cost) {// TODO: put into updateMethod
                $model->setAttribute('cost', $offer_sku_rules->cost);
            }
            return $this->updateOfferSkuRules($model);
        }

        $offer_sku_rules->validate();

        // TODO: Catch errors and exceptions
        if (!empty($offer_sku_rules->errors)) {
            FishHelper::debug($offer_sku_rules->errors);
        }


//        FishHelper::debug(111111);
        return $offer_sku_rules->save();
    }

    /**
     * @param OfferSkuRules $offer_sku_rules
     * @return bool
     */
    public function updateOfferSkuRules(OfferSkuRules $offer_sku_rules)
    {
        $offer_sku_rules->validate();

        // TODO: Catch errors and exceptions
        if (!empty($offer_sku_rules->errors)) {
            FishHelper::debug($offer_sku_rules->errors);
        }
        Yii::$app->getSession()->setFlash('success', 'Rule # ' . $offer_sku_rules->offer_sku_rule_id . ' updated successfully.');
        return $offer_sku_rules->save();
    }

    /**
     * @param $offer_id
     * @return static[]
     */
    public function offerSku($offer_id)
    {
        // TODO: JOIN SKU TABLES
        $sku_list = OfferSku::findAll(['offer_id' => $offer_id]);
        return $sku_list;
    }

    public function saveOfferSmsTemplate(Offer $offer, TargetAdvertGroup $advert, $request)
    {
        $offer->updateAttributes([
            'customer_send_sms' => isset($request['customer_send_sms']) ? $request['customer_send_sms'] : 0,
            'sms_text' => isset($request['customer_sms_text']) ? $request['customer_sms_text'] : '',
        ]);

        $offer->validate() ? $offer->save() : $offer->errors;

        $advert->updateAttributes([
            'send_sms' => isset($request['owner_send_sms']) ? $request['owner_send_sms'] : 0,
            'sms_text' => isset($request['owner_sms_text']) ? $request['owner_sms_text'] : '',
        ]);

        $advert->validate() ? $advert->save() : $advert->errors;
    }
}