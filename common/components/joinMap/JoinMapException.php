<?php
/**
 * Created by PhpStorm.
 * User: evild
 * Date: 9/3/18
 * Time: 5:36 PM
 */

namespace common\components\joinMap;

use yii\base\Exception;

class JoinMapException extends Exception
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'JoinMapException';
    }
}