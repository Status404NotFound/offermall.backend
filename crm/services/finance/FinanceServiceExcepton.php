<?php

namespace crm\services\finance;

use common\services\ServiceException;

class FinanceServiceExcepton extends ServiceException
{
    public function getName()
    {
        return 'FinanceServiceExcepton';
    }
}