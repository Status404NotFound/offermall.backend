<?php

namespace crm\services\webmaster\exceptions;

use yii\base\Exception;

class ChangeStatusException extends Exception
{
    public function getName()
    {
        return 'ChangeStatusException';
    }
}