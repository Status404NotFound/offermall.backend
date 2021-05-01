<?php

namespace common\services\offer;

use yii\base\Exception;

class OfferNotFoundException extends Exception
{
    public function getName()
    {
        return 'OfferNotFoundException';
    }
}