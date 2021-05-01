<?php

namespace crm\services\webmaster\exceptions;

class ServiceException extends \yii\base\Exception
{
    public function getName()
    {
        return 'ServiceException';
    }
}