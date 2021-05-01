<?php

namespace common\models\log\orderSku;

use yii\base\Exception;

class OrderSkuLogException extends Exception
{
    public function getName()
    {
        return 'OrderSkuLogException';
    }
}