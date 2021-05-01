<?php

namespace regorder\services\order\exceptions;

use yii\base\Exception;

class SaveCustomerException extends Exception
{
    public function getName()
    {
        return 'SaveCustomerException';
    }
}