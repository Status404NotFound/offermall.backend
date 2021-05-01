<?php
namespace crm\services\user;

use common\services\ServiceException;

class UserServiceException extends ServiceException
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'UserServiceException';
    }
}