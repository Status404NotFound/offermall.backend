<?php


namespace common\modules\user\commands;

use common\modules\user\helpers\Helper;
use common\modules\user\models\AuthItem;
use common\modules\user\models\search\AuthItemSearch;
use common\modules\user\traits\AuthManagerTrait;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\rbac\Item;
use yii\rbac\Role;
use yii\rbac\Rule;

/**
 * RBAC for users.
 *
 * @property \common\modules\user\Module $module
 *
 * @author makandy <makandy42@gmail.com>
 */
class RoleController extends Controller {
    use AuthManagerTrait;

    /**
     *  Help list.
     */
    public function actionHelp()
    {
        $this->stdout(Yii::t('user', 'You have the following commands:') . "\n", Console::FG_YELLOW);
        $message = "\t- " . Yii::t('user', 'List roles') . ":\t\t\t\t user/role\n";
        $message .= "\t- " . Yii::t('user', 'Create role') . ":\t\t\t\t user/role/create\n";
        $message .= "\t- " . Yii::t('user', 'Delete role') . ":\t\t\t\t user/role/delete\n";
        $message .= "\t- " . Yii::t('user', 'Assign child or permission') . ":\t user/role/assign\n";
        $message .= "\t- " . Yii::t('user', 'Revoke child or permission') . ":\t user/role/revoke\n";

        $this->stdout($message, Console::FG_GREEN);
    }

    /**
     * Writes a list of roles to the console.
     * Ex. - nameRole::nameRule (Description)
     */
    public function actionIndex() {
        $searchModel = new AuthItemSearch(['type' => Item::TYPE_ROLE]);
        $dataProvider = $searchModel->search(null);
        $message = '';

        $this->stdout(Yii::t('user', 'List roles:') . "\n", Console::FG_YELLOW);

        if ( !empty($dataProvider->allModels) ) {
            $arr = $dataProvider->allModels;
            /** @var Role $role */
            foreach ($arr as $role) {
                $message .= "\t- {$role->name}::{$role->ruleName}" .
                    (empty($role->description)?'':" ({$role->description})") . "\n";
            }
        } else {
            $message = "Not found!\n";
        }

        $this->stdout($message, Console::FG_GREEN);
    }

    /**
     * Create role.
     *
     * @param $name                 - name role
     * @param null $descriptions    - descriptions
     * @param null $ruleName        - name rule class
     */
    public function actionCreate($name, $descriptions = null, $ruleName = null) {
        $model = new AuthItem(null);

        if ( is_null($ruleName) ) {
            $ruleName = 'RouteRule';
        }

        $model->type = Item::TYPE_ROLE;
        $model->name = $name;
        $model->ruleName = $ruleName;
        $model->description = $descriptions;

        if ($model->validate() && $model->save()) {
            $this->stdout(Yii::t('user', 'Create role'). ": $name" . "\n", Console::FG_GREEN);
        } else {
            $this->stdout('Error: ' . print_r($model->errors,true) . "\n", Console::FG_RED);
        }
    }

    /**
     * @param $role
     */
    public function actionDelete($role)
    {
        if ($this->confirm(Yii::t('user', 'Are you sure? Deleted role can not be restored'))) {
            /** @var Rule $model */
            $model = $this->findModel($role);
            if ($model !== null) {
                if ($this->getAuthManager()->remove($model->item)) {
                    Helper::invalidate();
                    $this->stdout(Yii::t('user', 'Role has been deleted') . "\n", Console::FG_GREEN);
                } else {
                    $this->stdout(Yii::t('user', 'Error occurred while deleting role') . "\n", Console::FG_RED);
                }
            }
        }

    }

    /**
     * Assign items.
     *
     * @param $role
     * @param $assign
     */
    public function actionAssign($role, $assign)
    {
        $modelRole = $this->findModel($role);
        if (strpos($assign, '*') === false) {
            $modelAssign = $this->findModel($assign);
            $this->assign($modelRole, $modelAssign, $role);
        } else {
            if ( strlen($assign) > 1 ) {
                $permissions = $this->getAuthManager()->getPermissions();
                foreach ($permissions as $permission) {
                    $name = $permission->name;
                    if ( preg_match('`' . $assign . '`', $name)) {
                        $modelAssign = $this->findModel($name);
                        $this->assign($modelRole, $modelAssign, $role);
                    }
                }
            } else {
                $this->stdout('Pattern (' . strlen($assign) . ') not valid!' . "\n", Console::FG_RED);
            }
        }
    }

    /**
     * Assignment.
     *
     * @param AuthItem $modelRole
     * @param $modelAssign
     * @param $role
     * @return bool
     */
    private function assign(AuthItem $modelRole, AuthItem $modelAssign, $role) {
        if ( !is_null($modelRole) && !is_null($modelAssign) ) {
            if ( $this->isAssigned($modelAssign, $role) ) {
                $this->stdout('Connection already installed! Not assigned: ' . $modelAssign->name . "\n", Console::FG_RED);
            } else {
                $modelRole->addChildren((array)$modelAssign->name);
                $this->stdout('Assigned: ' . $modelAssign->name . "\n", Console::FG_GREEN);
                return true;
            }
        }

        return false;
    }

    /**
     * Assignment check.
     *
     * @param $modelAssign
     * @param $role
     * @return bool
     */
    private function isAssigned($modelAssign, $role) {
        $arrChildren = $this->getAuthManager()->getChildren($role);
        if ( isset($arrChildren[$modelAssign->name]) ) {
            return true;
        }
        return false;
    }

    /**
     * Assign or remove items.
     *
     * @param $role
     * @param $assign
     */
    public function actionRevoke($role, $assign) {
        $modelRole = $this->findModel($role);
        if (strpos($assign, '*') === false) {
            $modelAssign = $this->findModel($assign);
            $this->revoke($modelRole, $modelAssign, $role);
        } else {
            if ( strlen($assign) > 1 ) {
                $permissions = $this->getAuthManager()->getPermissions();
                foreach ($permissions as $permission) {
                    $name = $permission->name;
                    if ( preg_match('`' . $assign . '`', $name)) {
                        $modelAssign = $this->findModel($name);
                        $this->revoke($modelRole, $modelAssign, $role);
                    }
                }
            } else {
                $this->stdout('Pattern (' . strlen($assign) . ') not valid!' . "\n", Console::FG_RED);
            }
        }
    }

    /**
     * Revoke.
     *
     * @param AuthItem $modelRole
     * @param $modelAssign
     * @param $role
     * @return bool
     */
    private function revoke(AuthItem $modelRole, AuthItem $modelAssign, $role) {
        if ( !is_null($modelRole) && !is_null($modelAssign) && $this->isAssigned($modelAssign, $role) ) {
            $modelRole->removeChildren((array)$modelAssign->name);
            $this->stdout('Revoke: ' . $modelAssign->name . "\n", Console::FG_GREEN);
            return true;
        }
        $this->stdout('Connection not found! Not revoke: ' . $modelAssign->name . "\n", Console::FG_RED);

        return false;
    }

    /**
     * Finds the AuthItem model based on its primary key value.
     * If the model is not found, a show message and return null.
     * @param string $name
     * @return AuthItem|null the loaded model
     */
    protected function findModel($name) {
        $auth = $this->getAuthManager();
        $item = $auth->getItem($name);

        if ( !empty($item) ) {
            return new AuthItem($item);
        } else {
            $this->stdout("The requested item $name does not exist.\n", Console::FG_RED);
            return null;
        }
    }
}
