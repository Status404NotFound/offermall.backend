<?php

namespace common\services\delivery;

use yii\base\Exception;

class DeliveryException extends Exception
{
    public function getName()
    {
        return 'DeliveryException';
    }
}