<?php

namespace common\services\partner;

use common\models\order\Order;

interface PartnerInterface
{
    public function send(Order $order): array;

    public function setGeo(string $geo_iso);
}
