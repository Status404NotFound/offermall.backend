<?php
namespace webmaster\services\profile;

use yii\base\Exception;

class ProfileNotFoundException extends Exception
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'ProfileNotFoundException';
    }
}