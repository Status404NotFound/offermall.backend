<?php
/**
 * Created by PhpStorm.
 * User: evild
 * Date: 7/18/18
 * Time: 12:47 PM
 */

namespace common\services\cache;

use yii\base\Exception;

class CacheException extends Exception
{
    public function getName()
    {
        return 'CacheException';
    }
}