<?php

namespace common\services\order;

use yii\base\Exception;

class OrderException extends Exception
{
    public function getName()
    {
        return 'OrderException';
    }
}