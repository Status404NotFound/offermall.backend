<?php

namespace common\services\order\logic\commission\advert;

use common\services\order\logic\commission\BaseCommission;
use common\services\order\logic\commission\OrderCommissionInterface;

class OrderAdvertCommission extends BaseCommission implements OrderCommissionInterface
{
    public function getCommission(): float
    {
        if (!$this->order->targetAdvert) return 0.0;
        $use_rules = $this->order->targetAdvert->targetAdvertGroup->use_commission_rules;
        if ($use_rules == false) {
            $this->commission = (float)$this->order->targetAdvert->targetAdvertGroup->base_commission;
            $this->commission;
        } else {
            $target_advert_group_rules = $this->order->targetAdvert->targetAdvertGroup->targetAdvertGroupRules; // array
            $rules = [];
            foreach ($target_advert_group_rules as $advert_group_rule) {
                $rules[$advert_group_rule->amount] = $advert_group_rule->commission;
            }
            $exceeded_commission = $this->order->targetAdvert->targetAdvertGroup->exceeded_commission;
            if ($this->order->total_amount > count($target_advert_group_rules)) {
                $this->commission = (float)($rules[count($target_advert_group_rules)])
                    + (($this->order->total_amount - count($target_advert_group_rules)) * $exceeded_commission);
            } else if(isset($this->order->total_amount)) {
                $this->commission = (float)$rules[$this->order->total_amount];
            } else {
                $this->commission = (float)$rules[1];
            }
        }
        $this->setUsdCommission($this->order->targetAdvert->targetAdvertGroup->currency_id);
        return $this->commission;
    }

    public function getUsdCommission(): float
    {
        return parent::getUsdCommission();
    }
}