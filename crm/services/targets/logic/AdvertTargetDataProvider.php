<?php

namespace crm\services\targets\logic;

use Yii;
use common\helpers\FishHelper;
use common\models\geo\Geo;
use common\models\offer\Offer;
use common\models\offer\targets\advert\AdvertOfferTarget;
use common\models\offer\targets\advert\sku\TargetAdvertSku;
use common\models\offer\targets\advert\sku\TargetAdvertSkuRules;
use common\models\offer\targets\advert\TargetAdvert;
use common\models\offer\targets\advert\TargetAdvertGroup;
use common\models\offer\targets\advert\TargetAdvertGroupRules;
use common\models\offer\targets\advert\TargetAdvertView;
use common\models\order\OrderStatus;
use common\models\product\Product;
use common\models\product\ProductSku;
use common\models\product\SkuView;

class AdvertTargetDataProvider
{
    public $offer_id;
    public $page;
    public $errors = [];

    const ADVERT_TAB = 1;
    const WM_TAB = 2;
    const SKU_TAB = 3;
    const NOTIFY_TAB = 4;
    const BLOCK_SKU_TAB = 5;

    public function __construct($offer_id, $page = null)
    {
        $this->offer_id = $offer_id;
        $this->page = $page;
    }

    public function compareData()
    {
        switch ($this->page) {
            case self::ADVERT_TAB:
                return $this->advertTab();
                break;
            case self::WM_TAB:
                return $this->wmTab();
                break;
            case self::SKU_TAB:
                return $this->skuTab();
                break;
            case self::NOTIFY_TAB:
                return $this->notifyTab();
                break;
            case self::BLOCK_SKU_TAB:
                return $this->blockSkuTab();
                break;
            default:
                $this->errors['PageError'] = 'The page not selected';
                return false;
        }
    }

    /**
     * @return mixed
     */
    public function advertTab()
    {
        $offer_targets = AdvertOfferTarget::getActiveStatusesByOfferId($this->offer_id);
        foreach ($offer_targets as &$status) {
            $select = ['advert_offer_target_id', 'geo_id', 'geo_name', 'aot_active as active', 'wm_visible'];
            $where = ['offer_id' => $this->offer_id, 'advert_offer_target_status' => $status['advert_offer_target_status']];
            $groupBy = 'geo_id, active, wm_visible';
            $status['geo'] = TargetAdvertView::getStatusGeos($select, $where, $groupBy);
            foreach ($status['geo'] as &$geo) {
                $geo['advert'] = TargetAdvertGroup::getAdvertGroupByTargetId($geo['advert_offer_target_id']);
                foreach ($geo['advert'] as &$group) {
                    $group_adverts = TargetAdvert::getGroupAdvertsByGroupId($group['target_advert_group_id']);
                    $group['advert_group'] = [];
                    foreach ($group_adverts as $advert) {
                        $group['advert_group'][] = $advert['advert_id'];
                    }
                    $group['target_advert_commission_rules'] = TargetAdvertGroupRules::getAdvertGroupRulesByGroupId($group['target_advert_group_id']);;
                }
            }
        }
        return $offer_targets;
    }

    /**
     * @return mixed
     */
    public function wmTab()
    {
        $offer_targets = AdvertOfferTarget::getActiveStatusesByOfferId($this->offer_id, true);
        foreach ($offer_targets as &$target) {
            $target['advert_offer_target_name'] = $target['advert_offer_target_status'] == '40' ? 'Approved' : OrderStatus::attributeLabels(intval($target['advert_offer_target_status']));
            $select = ['geo_id', 'geo_name'];
            $where = ['offer_id' => $this->offer_id, 'advert_offer_target_status' => $target['advert_offer_target_status'],
                'aot_active' => 1, 'wm_visible' => 1];
            $groupBy = 'geo_id';
            $target['geo'] = TargetAdvertView::getStatusGeos($select, $where, $groupBy);
        }
        return $offer_targets;
    }

    /**
     * @return array|AdvertOfferTarget[]
     */
    public function skuTab()
    {
        $targets = AdvertOfferTarget::getStatusesForSkuTabByOfferId($this->offer_id);
        foreach ($targets as &$target) {
            $geo = Geo::findOne(['geo_id' => $target['geo_id']]);
            $target['geo_name'] = $geo->geo_name;
            $target['geo_iso'] = $geo->iso;
            $target['adverts'] = $this->getTargetAdvertsForSkuTab(
                $target['advert_offer_target_status'],
                $target['geo_id']);
            foreach ($target['adverts'] as &$advert) {
                $sku_array = $this->getAdvertSku($advert['target_advert_id']);
                foreach ($sku_array as &$sku) {
                    if ($sku['sku_id'] != null) {
                        $product_sku = ProductSku::findOne(['sku_id' => $sku['sku_id']]);
                        $sku['sku_name'] = $product_sku->sku_name;
                        $sku['geo_id'] = $product_sku->geo_id;
                        $sku['advert_id'] = $product_sku->advert_id;
                    }
                }
                $advert['sku'] = $sku_array;
            }
        }
        return $targets;
    }

    /**
     * @return array|AdvertOfferTarget[]
     */
    public function blockSkuTab()
    {
        $targets = AdvertOfferTarget::getStatusesForBlockSkuTabByOfferId($this->offer_id);
        foreach ($targets as &$target) {
            $geo = Geo::findOne(['geo_id' => $target['geo_id']]);
            $target['geo_name'] = $geo->geo_name;
            $target['geo_iso'] = $geo->iso;
            $target['adverts'] = $this->getTargetAdvertsForBlockSkuTab(
                $target['advert_offer_target_status'],
                $target['geo_id']);
            foreach ($target['adverts'] as &$advert) {
                $sku_array = $this->getAdvertBlockSku($advert['target_advert_id']);
                foreach ($sku_array as &$sku) {
                    if ($sku['sku_id'] != null) {
                        $product_sku = ProductSku::findOne(['sku_id' => $sku['sku_id']]);
                        $sku['sku_name'] = $product_sku->sku_name;
                        $sku['geo_id'] = $product_sku->geo_id;
                        $sku['advert_id'] = $product_sku->advert_id;
                    }
                }
                $advert['sku'] = $sku_array;
            }
        }
        return $targets;
    }

    /**
     * @return mixed
     */
    public function notifyTab()
    {
        $offer_notify = AdvertOfferTarget::getActiveStatusesByOfferId($this->offer_id);
        foreach ($offer_notify as &$status) {
            $status['advert_offer_target_status_name'] = OrderStatus::attributeLabels($status['advert_offer_target_status']);
            $select = ['advert_offer_target_id', 'geo_id', 'geo_name', 'geo_iso'];
            $where = ['offer_id' => $this->offer_id, 'advert_offer_target_status' => $status['advert_offer_target_status']];
            $groupBy = 'geo_id';
            $status['geo'] = TargetAdvertView::getStatusGeos($select, $where, $groupBy);
            foreach ($status['geo'] as &$geo) {
                $geo['advert'] = TargetAdvertGroup::getAdvertGroupNotifyByTargetId($geo['advert_offer_target_id']);
                foreach ($geo['advert'] as &$group) {
                    $group_adverts = TargetAdvert::getGroupAdvertsNotifyByGroupId($group['target_advert_group_id']);
                    $group['advert_name'] = [];
                    foreach ($group_adverts as $advert) {
                        $group['advert_name'] = $advert['advert'];
                    }
                }
            }
        }
        return $offer_notify;
    }

    public function getTargetAdvertsForSkuTab($advert_offer_target_status, $geo_id, $with_rules = false)
    {
        $target_adverts = TargetAdvertView::find()
            ->select(['target_advert_id', 'advert_id', 'advert_name', 'ta_active as is_active'])
            ->where(['offer_id' => $this->offer_id, 'advert_offer_target_status' => $advert_offer_target_status, 'geo_id' => $geo_id])
            ->asArray()
            ->all();
        if ($with_rules == true) {
            foreach ($target_adverts as &$advert) {
                $advert['rules'] = [];
                $advert['rules'][] = $this->getAdvertRulesForSkuTab($advert['target_advert_id'], $advert['advert_id']);
            }
        }
        return $target_adverts;
    }

    public function getTargetAdvertsForBlockSkuTab($advert_offer_target_status, $geo_id, $with_rules = false)
    {
        $query = TargetAdvertView::find()
            ->select(['target_advert_id', 'advert_id', 'advert_name'])
            ->where(['offer_id' => $this->offer_id, 'advert_offer_target_status' => $advert_offer_target_status, 'geo_id' => $geo_id]);

        if (!is_null(Yii::$app->user->identity->getOwnerId())) $query->andWhere(['advert_id' => Yii::$app->user->identity->getOwnerId()]);

        $target_adverts = $query
            ->asArray()
            ->all();
        if ($with_rules == true) {
            foreach ($target_adverts as &$advert) {
                $advert['rules'] = [];
                $advert['rules'][] = $this->getAdvertRulesForSkuTab($advert['target_advert_id'], $advert['advert_id']);
            }
        }
        return $target_adverts;
    }

    public function getAdvertSku($target_advert_id)
    {
        $advert_sku = TargetAdvertSku::find()
            ->where(['target_advert_id' => $target_advert_id])
            ->asArray()
            ->all();
        foreach ($advert_sku as &$sku) {
            $sku['rules'] = $this->getTargetAdvertSkuRules($target_advert_id, $sku['target_advert_sku_id'], $sku['sku_id']);
        }
        return $advert_sku;
    }

    public function getAdvertBlockSku($target_advert_id)
    {
        $advert_sku = TargetAdvertSku::find()
            ->where(['target_advert_id' => $target_advert_id])
            ->asArray()
            ->all();
        foreach ($advert_sku as &$sku) {
            $sku['rules'] = $this->getTargetAdvertSkuRules($target_advert_id, $sku['target_advert_sku_id'], $sku['sku_id']);
        }
        return $advert_sku;
    }

    public function getTargetAdvertSkuRules($target_advert_id, $target_advert_sku_id, $sku_id)
    {
        return TargetAdvertSkuRules::find()
            ->select(['amount', 'cost'])
            ->where(['target_advert_id' => $target_advert_id,
                'target_advert_sku_id' => $target_advert_sku_id,
                'sku_id' => $sku_id])
            ->asArray()->all();
    }

    public function getErrors()
    {
        return $this->errors;
    }
}