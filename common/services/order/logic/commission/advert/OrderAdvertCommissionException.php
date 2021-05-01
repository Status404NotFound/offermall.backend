<?php

namespace common\services\order\logic\commission\advert;

use yii\base\Exception;

class OrderAdvertCommissionException extends Exception
{
    public function getName()
    {
        return 'OrderAdvertCommissionException';
    }
}