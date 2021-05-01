<?php

namespace common\modules\user\traits;


use common\modules\user\components\DbManager;
use Yii;
use yii\rbac\ManagerInterface;

/**
 * Trait ModuleTrait
 *
 * @author makandy <makandy42@gmail.com>
 */
trait AuthManagerTrait
{
    /**
     * @return DbManager|ManagerInterface
     */
    public static function getAuthManager() {
        static $authManager = null;

        if ($authManager === null) {
            $authManager = Yii::$app->authManager;
        }

        return $authManager;
    }

    /**
     * @return DbManager|ManagerInterface
     */
    public static function authManager() {
        static $authManager = null;

        if ($authManager === null) {
            $authManager = Yii::$app->authManager;
        }

        return $authManager;
    }
}
