<?php

namespace common\services\order\logic\commission;

interface OrderCommissionInterface
{
    public function getCommission(): float;
    public function getUsdCommission(): float;
}