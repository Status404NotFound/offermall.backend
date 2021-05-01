<?php

namespace common\services\stock;

use common\services\ServiceException;

class StockServiceException extends ServiceException
{
    public function getName()
    {
        return 'StockServiceException';
    }
}