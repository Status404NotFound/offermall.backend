<?php

namespace common\models\log\orderInfo;

use yii\base\Exception;

class OrderInfoInstrumentException extends Exception
{
    public function getName()
    {
        return 'OrderInfoInstrumentException';
    }
}