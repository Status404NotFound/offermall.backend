<?php

namespace common\services\order\logic\commission;

use common\helpers\FishHelper;
use common\models\finance\Currency;
use common\models\finance\CurrencyRatePerDay;
use common\models\order\Order;

/**
 * Class BaseCommission
 * @package common\services\order\logic\commission
 */
class BaseCommission
{
    protected $order;
    protected $commission;
    protected $usd_commission = null;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->commission = (float)0;
    }

    public function getUsdCommission(): float
    {
        return $this->usd_commission ?? 0.0;
    }

    protected function setUsdCommission($currency_id)
    {
        if ($currency_id == Currency::USD) {
            $this->usd_commission = $this->commission;
        } else {
            $rate = CurrencyRatePerDay::getCurrencyRate($currency_id);
            $this->usd_commission = $this->commission / $rate;
        }
    }
}