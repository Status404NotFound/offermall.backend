<?php

namespace common\services;

class ServiceException extends \yii\base\Exception
{
    public function getName()
    {
        return 'ServiceException';
    }
}