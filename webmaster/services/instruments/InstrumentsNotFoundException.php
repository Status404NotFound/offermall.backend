<?php
namespace webmaster\services\instruments;

use yii\base\Exception;

class InstrumentsNotFoundException extends Exception
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'InstrumentsNotFoundException';
    }
}