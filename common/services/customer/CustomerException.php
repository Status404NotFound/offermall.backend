<?php

namespace common\services\customer;

use yii\base\Exception;

class CustomerException extends Exception
{
    public function getName()
    {
        return 'CustomerException';
    }
}