<?php

namespace common\services\order\logic\commission\wm;

use common\models\finance\Currency;
use common\services\order\logic\commission\BaseCommission;
use common\services\order\logic\commission\OrderCommissionInterface;

class OrderWmCommission extends BaseCommission implements OrderCommissionInterface
{
    public function getCommission(): float
    {
        if (!$this->order->targetWm) return (float)0;
        $use_rules = $this->order->targetWm->targetWmGroup->use_commission_rules;
        if ($use_rules == false) {
            $this->commission = (float)$this->order->targetWm->targetWmGroup->base_commission;
        } else {
            $target_wm_group_rules = $this->order->targetWm->targetWmGroup->targetWmGroupRules; // array
            $rules = [];
            foreach ($target_wm_group_rules as $wm_group_rule) {
                $rules[$wm_group_rule->amount] = $wm_group_rule->commission;
            }
            $exceeded_commission = $this->order->targetWm->targetWmGroup->exceeded_commission;
            if ($this->order->total_amount > count($target_wm_group_rules)) {
                $this->commission = (float)(($this->order->total_amount - count($target_wm_group_rules)) * $exceeded_commission)
                    + $rules[count($target_wm_group_rules)];
            } else {
                $this->commission = (float)$rules[$this->order->total_amount];
            }
        }
        $this->setUsdCommission(Currency::USD);
        return $this->commission;
    }

    public function getUsdCommission(): float
    {
        return parent::getUsdCommission();
    }
}