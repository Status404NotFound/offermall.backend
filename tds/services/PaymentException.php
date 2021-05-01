<?php

namespace tds\services;

use common\services\ServiceException;

class PaymentException extends ServiceException
{
    public function getName()
    {
        return 'PaymentException';
    }
}