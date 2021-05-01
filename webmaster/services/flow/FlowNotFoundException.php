<?php
namespace webmaster\services\flow;

use yii\base\Exception;

class FlowNotFoundException extends Exception
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'FlowNotFoundException';
    }
}