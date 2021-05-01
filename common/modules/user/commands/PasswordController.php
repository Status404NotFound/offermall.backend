<?php


namespace common\modules\user\commands;

use common\modules\user\UserFinder;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Updates user's password.
 *
 * @property \common\modules\user\Module $module
 *
 * @author makandy <makandy42@gmail.com>
 */
class PasswordController extends Controller
{
    /** @var UserFinder */
    protected $UserFinder;

    /**
     * @param string           $id
     * @param \yii\base\Module $module
     * @param UserFinder           $UserFinder
     * @param array            $config
     */
    public function __construct($id, $module, UserFinder $UserFinder, $config = [])
    {
        $this->UserFinder = $UserFinder;
        parent::__construct($id, $module, $config);
    }

    /**
     * Updates user's password to given.
     *
     * @param string $search   Email or username
     * @param string $password New password
     */
    public function actionIndex($search, $password)
    {
        $user = $this->UserFinder->findUserByUsernameOrEmail($search);
        if ($user === null) {
            $this->stdout(Yii::t('user', 'User is not found') . "\n", Console::FG_RED);
        } else {
            if ($user->resetPassword($password)) {
                $this->stdout(Yii::t('user', 'Password has been changed') . "\n", Console::FG_GREEN);
            } else {
                $this->stdout(Yii::t('user', 'Error occurred while changing password') . "\n", Console::FG_RED);
            }
        }
    }
}
