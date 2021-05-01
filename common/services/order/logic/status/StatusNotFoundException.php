<?php

namespace common\services\order\logic\status;

use yii\base\Exception;

class StatusNotFoundException extends Exception
{
    public function getName()
    {
        return 'StatusNotFoundException';
    }
}