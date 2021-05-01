<?php

namespace regorder\services\order\exceptions;

use yii\base\Exception;

class TargetWmNotFoundException extends Exception
{
    public function getName()
    {
        return 'TargetWmNotFoundException';
    }
}