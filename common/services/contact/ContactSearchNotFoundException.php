<?php
namespace common\services\contact;

use yii\base\Exception;

class ContactSearchNotFoundException extends Exception
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'ContactSearchNotFoundException';
    }
}