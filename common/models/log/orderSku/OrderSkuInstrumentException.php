<?php

namespace common\models\log\orderSku;

use yii\base\Exception;

class OrderSkuInstrumentException extends Exception
{
    public function getName()
    {
        return 'OrderSkuInstrumentException';
    }
}