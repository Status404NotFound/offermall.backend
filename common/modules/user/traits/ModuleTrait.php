<?php

namespace common\modules\user\traits;


use common\modules\user\Module;
use Yii;

/**
 * Trait ModuleTrait
 *
 * @author makandy <makandy42@gmail.com>
 */
trait ModuleTrait {
    /**
     * @return null|\yii\base\Module|Module
     */
    public static function getModule() {
        return Yii::$app->getModule('user');
    }

    /**
     * @return null|\yii\base\Module|Module
     */
    public static function module() {
        return Yii::$app->getModule('user');
    }
}
