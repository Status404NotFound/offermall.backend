<?php

namespace common\services\order\logic\commission\delivery;

use yii\base\Exception;

class OrderWmCommissionException extends Exception
{
    public function getName()
    {
        return 'OrderWmCommissionException';
    }
}