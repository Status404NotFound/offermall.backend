<?php


namespace common\modules\user\commands;

use common\modules\user\models\search\AuthItemSearch;
use common\modules\user\models\Assignment;
use common\modules\user\traits\AuthManagerTrait;
use common\modules\user\UserFinder;
use Yii;
use yii\console\Controller;
use yii\db\Query;
use yii\helpers\Console;
use yii\rbac\Item;
use yii\rbac\Role;

/**
 * RBAC for users.
 *
 * @property \common\modules\user\Module $module
 *
 * @author makandy <makandy42@gmail.com>
 */
class AssignmentController extends Controller {
    use AuthManagerTrait;

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
     *  Help list.
     */
    public function actionHelp()
    {
        $this->stdout(Yii::t('user', 'You have the following commands:') . "\n", Console::FG_YELLOW);
        $message = "\t- " . Yii::t('user', 'List assign') . ":\t\t user/assignment\n";
        $message .= "\t- " . Yii::t('user', 'Assign user and role') . ":\t user/assignment/assign\n";
        $message .= "\t- " . Yii::t('user', 'Revoke user and role') . ":\t user/assignment/revoke\n";

        $this->stdout($message, Console::FG_GREEN);
    }

    /**
     * Writes a list of roles to the console.
     * Ex. - nameRole::nameRule (Description)
     */
    public function actionIndex() {
        $arrAssign = $this->getModelAssignment()->all();
        $message = '';

        $this->stdout("List of roles assigned to users:\n", Console::FG_YELLOW);
        foreach ($arrAssign as $assign) {
            $message .= $assign['item_name'] . ':user_id(' . $assign['user_id'] . ")\n";
        }
        $this->stdout(print_r($message,true), Console::FG_GREEN);
    }

    /**
     * Assign role from user.
     *
     * @param $user string
     * @param $role string
     * @internal param $assign
     */
    public function actionAssign($user, $role) {
        $modelUser = $this->UserFinder->findUserByUsernameOrEmail($user);

        if ($modelUser === null || $this->getAuthManager()->getRole($role) === null) {
            $this->stdout(Yii::t('user', 'User is not found') . "\n", Console::FG_RED);
        } else {
            $isAssignment = $this->getModelAssignment()
                ->where(['item_name' => $role, 'user_id' => $modelUser->id])->exists();

            if ( $isAssignment ) {
                $this->stdout(Yii::t('user','Connection already installed!') . "\n", Console::FG_RED);
            } else {
                $model = new Assignment($modelUser->id);
                $model->assign((array)$role);
                $this->stdout(Yii::t('user','Connect!') . "\n", Console::FG_GREEN);
            }
        }
    }

    /**
     * Revoke with the role.
     *
     * @param $user
     * @param $role
     * @internal param $assign
     */
    public function actionRevoke($user, $role) {
        $modelUser = $this->UserFinder->findUserByUsernameOrEmail($user);

        if ($modelUser === null || $this->getAuthManager()->getRole($role) === null) {
            $this->stdout(Yii::t('user', 'User is not found') . "\n", Console::FG_RED);
        } else {
            $isAssignment = $this->getModelAssignment()
                ->where(['item_name' => $role, 'user_id' => $modelUser->id])->exists();

            if ( !$isAssignment ) {
                $this->stdout(Yii::t('user','Connection not found!') . "\n", Console::FG_RED);
            } else {
                $model = new Assignment($modelUser->id);
                $model->revoke((array)$role);
                $this->stdout(Yii::t('user','Revoke!') . "\n", Console::FG_GREEN);
            }
        }
    }

    /**
     * Get model table assignment.
     *
     * @return Query
     */
    protected function getModelAssignment() {
        return (new Query())->from($this->getAuthManager()->assignmentTable);
    }
}
