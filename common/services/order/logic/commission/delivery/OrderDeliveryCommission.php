<?php

namespace common\services\order\logic\commission\delivery;

use common\helpers\FishHelper;
use common\models\finance\Currency;
use common\models\offer\targets\advert\sku\TargetAdvertSku;
use common\models\offer\targets\advert\sku\TargetAdvertSkuRules;
use common\models\order\Order;
use common\models\order\OrderSku;
use common\services\order\logic\commission\BaseCommission;
use common\services\order\logic\commission\OrderCommissionInterface;
use yii\helpers\ArrayHelper;

class OrderDeliveryCommission extends BaseCommission implements OrderCommissionInterface
{
    private $orderSku;
    private $targetAdvertSku;

    private $exceeded_cost;
    private $exceeded_amount;
    private $use_extended_rules;  // TODO: Hardcode variant of commission
    private $is_upsale;  // TODO: Calculate Upsale Costs
    private $use_rules;
    private $rules;

    private $currency_id;

    // TODO: 1. Убрать __construct(), Order передавать в getCommission(Order $order)
    // TODO: 2. Поубирать поля
    // TODO: 3. Проверка на наличие кол-ва в правилах, отсутствие значений и пр. валидация, обработка ошибок, исключения
    /**
     * OrderDeliveryCommission constructor.
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        parent::__construct($order);
        $this->orderSku = OrderSku::findForOrderCommission($this->order->order_id);
        $this->targetAdvertSku = TargetAdvertSku::findForOrderCommission($this->order->target_advert_id);
//        $currency = Currency::findOne(['country_id' => $this->order->targetAdvert->targetAdvertGroup->advertOfferTarget->geo_id]);
        $currency = Currency::findOne(['country_id' => $this->order->customer->country_id]);
        $this->currency_id = $currency->currency_id;
    }

    /**
     * @return float
     */
    public function getCommission(): float
    {
        foreach ($this->targetAdvertSku as $sku_id => $target_advert_sku) {
            $this->use_rules = $target_advert_sku['use_sku_cost_rules'];
            // TODO: Найти место для расширенных хардкодных правил (вынести в отдельный класс)
            if ($this->use_rules == true) {
                /** use_rules = true */
                $this->rules = TargetAdvertSkuRules::findAllForOrderCommission($this->order->target_advert_id);
                if ($target_advert_sku['sku_id'] === null) {
                    /** for_all_sku = true && use_rules = true */
//                    $this->commission = $this->allSkuUseRules($target_advert_sku);
                    $this->commission = $this->allSkuOneRule();
                    $this->setUsdCommission($this->currency_id);
                    return $this->commission;
                } elseif ($target_advert_sku['use_extended_rules'] == 1) {
                    /** for_all_sku = false && use_rules = true */
                    $this->commission = $this->currentSkuUseRules();
                } else {
                    $this->commission = $this->allSkuOneRule();
                    $this->setUsdCommission($this->currency_id);
                    return $this->commission;
                }
            } else {
                /** use_rules = false */
                if ($target_advert_sku['sku_id'] === null) {
                    /** use_rules = false && for_all_sku = true */
                    $this->commission = $this->allSkuNoRules($target_advert_sku);
                } else {
                    /** use_rules = false && for_all_sku = false */
                    $this->commission = $this->currentSkuNoRules();
                }
            }
        }
        $this->setUsdCommission($this->currency_id);
        return $this->commission;
    }

    public function getUsdCommission(): float
    {
        return parent::getUsdCommission();
    }

//    /**
//     * @param $target_advert_sku
//     * @return float
//     * @throws OrderDeliveryCommissionException
//     */
//    private function allSkuUseRules($target_advert_sku)
//    {
//        $this->rules = ArrayHelper::map($this->rules, 'amount', 'cost');
//        $this->commission = (float)0;
//        foreach ($this->orderSku as $order_sku_id => $order_sku) {
//            $this->exceeded_amount = count($this->rules);
//            $this->exceeded_cost = $target_advert_sku['exceeded_cost'];
//            $sku_commission = $this->getSkuCommission($order_sku['amount']);
//            $this->commission += $sku_commission;
//            $this->saveOrderSku($order_sku['sku_id'], $sku_commission);
//        }
//        return $this->commission;
//    }

    private function allSkuOneRule()
    {
        $this->commission = (float)0;
        $base_amount = 0;
        $upsales = [];
        $forAll = null;
        unset($this->rules);
        foreach ($this->orderSku as $sku_id => $order_sku) {
            if (!isset($this->targetAdvertSku[$sku_id]) &&
                isset($this->targetAdvertSku[null]) &&
                count($this->targetAdvertSku) === 1
            ) $forAll = 1;
            if ($this->targetAdvertSku[$forAll ? null : $sku_id]['is_upsale'] == true) {
                $upsales[$sku_id] = $order_sku;
            } else {
                $base_amount += $order_sku['amount'];
                $rules = TargetAdvertSkuRules::findSkuRulesForOrderCommission($this->order->target_advert_id, $forAll ? null : $sku_id);
                $this->exceeded_cost = $this->targetAdvertSku[$forAll ? '' : $sku_id]['exceeded_cost'];  // By last sku
            }
            if (isset($rules)) {
                foreach ($rules as $rule) {
                    if ($rule['sku_id'] == ($forAll ? null : $sku_id)) {
                        $this->rules[$rule['amount']] = $rule['cost'];
                    }
                }
            }
        }

        // TODO: Check if ONLY upsale sku - reject setting
        // TODO: Check if no rules (for ONLY uplale sku) - reject setting
        $this->exceeded_amount = count($this->rules);
        $this->commission = $this->getSkuCommission($base_amount);

        $upsales_commission = 0.0;
        foreach ($upsales as $sku_id => $upsale) {
            $upsales_commission += $this->targetAdvertSku[$forAll ? null : $sku_id]['base_cost'] * $upsale['amount'];
        }

        $base_commission = (float)$this->commission / (float)$base_amount;
        foreach ($this->orderSku as $sku_id => $order_sku) {
            if ($this->targetAdvertSku[$forAll ? null : $sku_id]['is_upsale'] == true) {
                $base_commission = $this->targetAdvertSku[$forAll ? null : $sku_id]['base_cost'];
            }
            if (!$this->saveOrderSku($sku_id, $base_commission))
                throw new OrderDeliveryCommissionException('Failed to save OrderSku cost.');
        }
        return $this->commission + $upsales_commission;
    }

    /**
     * @return float
     * @throws OrderDeliveryCommissionException
     */
    private function currentSkuUseRules()
    {
        $this->commission = (float)0;
        foreach ($this->orderSku as $order_sku_id => $order_sku) {
            $rules = TargetAdvertSkuRules::findSkuRulesForOrderCommission($this->order->target_advert_id, $order_sku_id);
            unset($this->rules);
            foreach ($rules as $rule) {
                if ($rule['sku_id'] == $order_sku_id) {
                    $this->rules[$rule['amount']] = (float)$rule['cost'];
                }
            }
            $this->exceeded_amount = count($this->rules);
            $this->exceeded_cost = (float)$this->targetAdvertSku[$order_sku_id]['exceeded_cost'];
            $this->is_upsale = $this->targetAdvertSku[$order_sku_id]['is_upsale'];
            $sku_commission = $this->getSkuCommission($order_sku['amount']);
            $this->commission += $sku_commission;
            if (!$this->saveOrderSku($order_sku['sku_id'], (float)$sku_commission))
                throw new OrderDeliveryCommissionException('Failed to save OrderSku cost.');
        }
        return $this->commission;
    }

    /**
     * @param $target_advert_sku
     * @return float|int
     */
    private function allSkuNoRules($target_advert_sku)
    {
        $this->commission = (float)0;
        $this->commission = $this->order->total_amount * $target_advert_sku['base_cost'];
        return $this->commission;
    }

    /**
     * @return float
     */
    private function currentSkuNoRules()
    {
        $this->commission = (float)0;
        foreach ($this->orderSku as $order_sku_id => $order_sku) {
            $this->commission += $order_sku['amount'] * $this->targetAdvertSku[$order_sku_id]['base_cost'];
        }
        return $this->commission;
    }

    /**
     * @param $amount
     * @return float
     */
    private function getSkuCommission($amount): float
    {
        if ($amount <= $this->exceeded_amount) {
            $sku_cost = (float)$this->rules[$amount];
        } else {
            $sku_cost = $this->rules[$this->exceeded_amount] + (($amount - $this->exceeded_amount) * $this->exceeded_cost);
        }
        return $sku_cost;
    }

    /**
     * @param $sku_id
     * @param $sku_cost
     * @return array|bool
     */
    public function saveOrderSku($sku_id, $sku_cost)
    {
        $orderSku = OrderSku::findOne(['order_id' => $this->order->order_id, 'sku_id' => $sku_id]);
        $orderSku->cost = $sku_cost;

        if (!$orderSku->save())
            FishHelper::debug($orderSku->errors);
//        throw new OrderDeliveryCommissionException('Failed to save OrderSku cost.');

        return true;
    }
}
