<?php
namespace common\services\finance;

use common\services\ServiceException;

class FinanceCommonException extends ServiceException
{
    public function getName()
    {
        return 'FinanceCommonException';
    }
}