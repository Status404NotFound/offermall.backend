<?php

namespace common\services\offer\exceptions;

use common\services\ServiceException;

class AdvertServiceException extends ServiceException
{
    public function getName()
    {
        return 'AdvertServiceException';
    }
}