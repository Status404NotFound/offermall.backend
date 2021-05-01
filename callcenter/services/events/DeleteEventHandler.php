<?php
/**
 * Created by PhpStorm.
 * User: ihor
 * Date: 26.06.17
 * Time: 14:37
 */

namespace callcenter\services\events;

use odannyc\Yii2SSE\SSEBase;

class DeleteEventHandler extends SSEBase
{
    public function check()
    {
        return true;
    }

    public function update()
    {
        return "Delete";
    }
}