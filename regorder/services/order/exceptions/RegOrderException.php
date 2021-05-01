<?php

namespace regorder\services\order\exceptions;

use yii\base\Exception;

class RegOrderException extends Exception
{
    public function getName()
    {
        return 'RegOrderException';
    }
}