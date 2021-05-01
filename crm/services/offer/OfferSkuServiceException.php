<?php

namespace crm\services\offer;

use common\services\ServiceException;

class OfferSkuServiceException extends ServiceException
{
    public function getName()
    {
        return 'OfferSkuServiceException';
    }
}