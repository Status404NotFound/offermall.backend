<?php

namespace crm\services\finstrip;

use common\services\ServiceException;

class FinstripServiceExcepton extends ServiceException
{
    public function getName()
    {
        return 'FinstripServiceExcepton';
    }
}