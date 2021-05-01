<?php

namespace common\modules\user;


use yii\helpers\Inflector;

class Module  extends \yii\base\Module {
    const VERSION = '0.0.1';

    /** Email is changed right after user enter's new email address. */
    const STRATEGY_INSECURE = 0;

    /** Email is changed after user clicks confirmation link sent to his new email address. */
    const STRATEGY_DEFAULT = 1;

    /** Email is changed after user clicks both confirmation links sent to his old and new email addresses. */
    const STRATEGY_SECURE = 2;

    /** Whether user has to confirm his account.  */
    const CONFIRM_USER = 0;

    /** Whether admin has to confirm user account. */
    const CONFIRM_ADMIN = 1;

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'common\modules\user\controllers';

    /** @var bool Whether to show flash messages. */
    public $enableFlashMessages = true;

    /** @var bool Whether to enable registration. */
    public $enableRegistration = true;

    /** @var bool Whether to remove password field from registration form. */
    public $enableGeneratingPassword = true;

    /** @var bool Who must confirm a user account. */
    public $enableConfirmation = self::CONFIRM_ADMIN;

    /** @var bool Whether to enable password recovery. */
    public $enablePasswordRecovery = true;

    /** @var bool Whether user can remove his account */
    public $enableAccountDelete = false;

    /** @var bool Enable the 'impersonate as another user' function */
    public $enableImpersonateUser = true;

    /** @var int Email changing strategy. */
    public $emailChangeStrategy = self::STRATEGY_DEFAULT;

    /** @var int The time you want the user will be remembered without asking for credentials. */
    public $rememberFor = 1209600; // two weeks

    /** @var int The time before a confirmation token becomes invalid. */
    public $confirmWithin = 86400; // 24 hours

    /** @var int The time before a recovery token becomes invalid. */
    public $recoverWithin = 21600; // 6 hours

    /** @var int Cost parameter used by the Blowfish hash algorithm. */
    public $cost = 10;

    /** @var array An array of administrator's user names. */
    public $admins = ['Admin', 'admin'];

    /** @var string The Administrator permission name. */
    public $adminPermission;

    /** @var array Mailer configuration */
    public $mailer = [];

    /** @var array Model map */
    public $modelMap = [];

    /**
     * @var string The prefix for user module URL.
     *
     * @See [[GroupUrlRule::prefix]]
     */
    public $urlPrefix = 'user';

    /**
     * @var boolean If true then AccessControl only check if route are registered.
     */
    public $onlyRegisteredRoute = true;

    /**
     * @var boolean If false then AccessControl will check without Rule.
     */
    public $strict = true;

    /**
     * @var bool Is the user module in DEBUG mode? Will be set to false automatically
     * if the application leaves DEBUG mode.
     */
    public $debug = true;

    /** @var array The rules to be used in URL management. */
    public $urlRules = [
        '<id:\d+>'                               => 'profile/show',
        '<action:(login|logout)>'                => 'security/<action>',
        '<action:(register|resend)>'             => 'registration/<action>',
        'confirm/<id:\d+>/<code:[A-Za-z0-9_-]+>' => 'registration/confirm',
        'forgot'                                 => 'recovery/request',
        'recover/<id:\d+>/<code:[A-Za-z0-9_-]+>' => 'recovery/reset',
        'settings/<action:\w+>'                  => 'settings/<action>'
    ];

    /**
     * @var array
     * @see [[menus]]
     */
    private $_menus = [];

    /**
     * @var array
     * @see [[menus]]
     */
    private $_coreItems = [
        'user' => 'Users',
        'assignment' => 'Assignments',
        'role' => 'Roles',
        'permission' => 'Permissions',
        'route' => 'Routes',
        'rule' => 'Rules',
        'menu' => 'Menus',
    ];

    /**
     * @var array
     * @see [[items]]
     */
    private $_normalizeMenus;

    /**
     * @var string Default url for breadcrumb
     */
    public $defaultUrl;

    /**
     * @var string Default url label for breadcrumb
     */
    public $defaultUrlLabel;

    public function userConfirmationAccount() {
        return $this->enableConfirmation === self::CONFIRM_USER;
    }

    public function adminConfirmationAccount() {
        return $this->enableConfirmation === self::CONFIRM_ADMIN;
    }



    /**
     * Get available menu.
     * @return array
     */
    public function getMenus()
    {
        if ($this->_normalizeMenus === null) {
            $mid = '/' . $this->getUniqueId() . '/';
            // resolve core menus
            $this->_normalizeMenus = [];

            $db = \Yii::$app->getDb();
            $conditions = [
                'user' => $db && $db->schema->getTableSchema('{{%user}}'),
                'assignment' => ($userClass = \Yii::$app->getUser()->identityClass) && is_subclass_of($userClass, 'yii\db\BaseActiveRecord'),
                'menu' => $db && $db->schema->getTableSchema('{{%menu}}'),
            ];
            foreach ($this->_coreItems as $id => $label) {
                if (!isset($conditions[$id]) || $conditions[$id]) {
                    $this->_normalizeMenus[$id] = ['label' => \Yii::t('user', $label), 'url' => [$mid . $id]];
                }
            }
            foreach (array_keys($this->controllerMap) as $id) {
                $this->_normalizeMenus[$id] = ['label' => \Yii::t('user', Inflector::humanize($id)), 'url' => [$mid . $id]];
            }

            // user configure menus
            foreach ($this->_menus as $id => $value) {
                if (empty($value)) {
                    unset($this->_normalizeMenus[$id]);
                    continue;
                }
                if (is_string($value)) {
                    $value = ['label' => $value];
                }
                $this->_normalizeMenus[$id] = isset($this->_normalizeMenus[$id]) ? array_merge($this->_normalizeMenus[$id], $value)
                    : $value;
                if (!isset($this->_normalizeMenus[$id]['url'])) {
                    $this->_normalizeMenus[$id]['url'] = [$mid . $id];
                }
            }
        }
        return $this->_normalizeMenus;
    }

    /**
     * Set or add available menu.
     * @param array $menus
     */
    public function setMenus($menus)
    {
        $this->_menus = array_merge($this->_menus, $menus);
        $this->_normalizeMenus = null;
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            /* @var $action \yii\base\Action */
            $view = $action->controller->getView();

            $view->params['breadcrumbs'][] = [
                'label' => ($this->defaultUrlLabel ?: \Yii::t('user', 'User')),
                'url' => ['/' . ($this->defaultUrl ?: $this->uniqueId)],
            ];
            return true;
        }
        return false;
    }
}