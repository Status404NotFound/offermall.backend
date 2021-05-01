<?php

namespace regorder\services\order\exceptions;

use yii\base\Exception;

class OrderAdvertServiceException extends Exception
{
    public function getName()
    {
        return 'OrderAdvertServiceException';
    }
}