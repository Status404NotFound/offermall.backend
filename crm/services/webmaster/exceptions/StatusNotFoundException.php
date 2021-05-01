<?php

namespace crm\services\webmaster\exceptions;

use yii\base\Exception;

class StatusNotFoundException extends Exception
{
    public function getName()
    {
        return 'StatusNotFoundException';
    }
}