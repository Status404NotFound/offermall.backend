<?php

namespace crm\services\webmaster\exceptions;

use yii\base\Exception;

class StealNotFoundException extends Exception
{
    public function getName()
    {
        return 'StealNotFoundException';
    }
}