<?php

namespace common\services\customer;

use yii\base\Exception;

class CustomerNotFoundException extends Exception
{
    public function getName()
    {
        return 'CustomerNotFoundException';
    }
}