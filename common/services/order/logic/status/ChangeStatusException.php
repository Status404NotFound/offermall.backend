<?php

namespace common\services\order\logic\status;

use yii\base\Exception;

class ChangeStatusException extends Exception
{
    public function getName()
    {
        return 'ChangeStatusException';
    }
}