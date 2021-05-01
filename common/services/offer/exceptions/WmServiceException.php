<?php

namespace common\services\offer\exceptions;

use common\services\ServiceException;

class WmServiceException extends ServiceException
{
    public function getName()
    {
        return 'WmServiceException';
    }
}