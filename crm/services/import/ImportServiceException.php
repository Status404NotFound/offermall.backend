<?php

namespace crm\services\import;

use common\services\ServiceException;

class ImportServiceException extends ServiceException
{
    public function getName()
    {
        return 'ImportServiceException';
    }
}