<?php

namespace common\services\offer\exceptions;

use common\services\ServiceException;

class OfferServiceException extends ServiceException
{
    public function getName()
    {
        return 'OfferServiceException';
    }
}