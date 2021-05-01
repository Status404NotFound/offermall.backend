<?php

namespace webmaster\services\finance;

use common\services\ServiceException;

class FinanceServiceException extends ServiceException
{
    public function getName()
    {
        return 'FinanceServiceException';
    }
}