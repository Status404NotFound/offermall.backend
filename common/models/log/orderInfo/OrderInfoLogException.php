<?php

namespace common\models\log\orderInfo;

use yii\base\Exception;

class OrderInfoLogException extends Exception
{
    public function getName()
    {
        return 'OrderInfoLogException';
    }
}