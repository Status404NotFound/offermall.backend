<?php

namespace regorder\services\order\exceptions;

use yii\base\Exception;

class SaveOrderException extends Exception
{
    public function getName()
    {
        return 'SaveOrderException';
    }
}