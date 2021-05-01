<?php
namespace crm\services\webmaster\exceptions;

use yii\base\Exception;

class OfferNotFoundException extends Exception
{
    public function getName()
    {
        return 'OfferNotFoundException';
    }
}