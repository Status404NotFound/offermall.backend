<?php


namespace common\modules\user\commands;

use common\modules\user\UserFinder;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Deletes a user.
 *
 * @property \common\modules\user\Module $module
 *
 * @author makandy <makandy42@gmail.com>
 */
class DeleteController extends Controller
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
     * Deletes a user.
     *
     * @param string $search Email or username
     */
    public function actionIndex($search)
    {
        if ($this->confirm(Yii::t('user', 'Are you sure? Deleted user can not be restored'))) {
            $user = $this->UserFinder->findUserByUsernameOrEmail($search);
            if ($user === null) {
                $this->stdout(Yii::t('user', 'User is not found') . "\n", Console::FG_RED);
            } else {
                if ($user->delete()) {
                    $this->stdout(Yii::t('user', 'User has been deleted') . "\n", Console::FG_GREEN);
                } else {
                    $this->stdout(Yii::t('user', 'Error occurred while deleting user') . "\n", Console::FG_RED);
                }
            }
        }
    }
}
