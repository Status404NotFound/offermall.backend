<?php

namespace common\services\order;

use yii\base\Exception;

class OrderNotFoundException extends Exception
{
    public function getName()
    {
        return 'OrderNotFoundException';
    }
}