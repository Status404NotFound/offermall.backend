<?php

namespace common\services\order\logic\commission\delivery;

use yii\base\Exception;

class OrderDeliveryCommissionException extends Exception
{
    public function getName()
    {
        return 'OrderDeliveryCommissionException';
    }
}