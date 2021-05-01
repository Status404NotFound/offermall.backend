<?php

namespace common\services\log;

use common\services\ServiceException;

class LogServiceException extends ServiceException
{
    public function getName()
    {
        return 'LogServiceException';
    }
}