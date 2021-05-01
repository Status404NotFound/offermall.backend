<?php

namespace common\services\order\logic\status;

use yii\base\Exception;

class OrderStatusReasonNotFoundException extends Exception
{
    public function getName()
    {
        return 'OrderStatusReasonNotFoundException';
    }
}