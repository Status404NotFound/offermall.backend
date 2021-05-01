<?php


//namespace common\modules\user\models\forms;

use common\modules\user\Module;
use common\modules\user\UserFinder;
use common\modules\user\helpers\Password;
use common\modules\user\models\tables\User;
use common\modules\user\traits\ModuleTrait;
use yii\db\Expression;
use Yii;
use yii\base\Model;

/**
 * LoginForm get user's login and password, validates them and logs the user in. If user has been blocked, it adds
 * an error to login form.
 *
 * @property Module $module
 * @author makandy <makandy42@gmail.com>
 */
class LoginForm extends Model
{
    use ModuleTrait;

    /** @var string User's email or username */
    public $login;

    /** @var string User's plain password */
    public $password;

    /** @var string Whether to remember the user */
    public $rememberMe = false;

    /** @var \common\modules\user\models\tables\User */
    protected $user;

    /** @var UserFinder */
    protected $UserFinder;

    /**
     * @param UserFinder $UserFinder
     * @param array  $config
     */
    public function __construct(UserFinder $UserFinder, $config = [])
    {
        $this->UserFinder = $UserFinder;
        parent::__construct($config);
    }

    /**
     * Gets all users to generate the dropdown list when in debug mode.
     *
     * @return array
     */
    public static function loginList()
    {
        return User::getUserList();
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'login'      => Yii::t('user', 'Login'),
            'password'   => Yii::t('user', 'Password'),
            'rememberMe' => Yii::t('user', 'Remember me next time'),
        ];
    }

    /** @inheritdoc */
    public function rules()
    {
        $rules = [
            'loginTrim' => ['login', 'trim'],
            'requiredFields' => [['login'], 'required'],
            'confirmationValidate' => [
                'login',
                function ($attribute) {
                    if ($this->user !== null) {
                        if (!$this->user->getIsConfirmed()) {
                            if ( $this->module->userConfirmationAccount() ) {
                                $this->addError($attribute, Yii::t('user', 'You need to confirm your email address'));
                            } else {
                                $this->addError($attribute, Yii::t('user', 'You need confirmation from the administration'));
                            }
                        }
                        if ($this->user->getIsBlocked()) {
                            $this->addError($attribute, Yii::t('user', 'Your account has been blocked'));
                        }
                    }
                }
            ],
            'rememberMe' => ['rememberMe', 'boolean'],
        ];

        if (!$this->module->debug) {
            $rules = array_merge($rules, [
                'requiredFields' => [['login', 'password'], 'required'],
                'passwordValidate' => [
                    'password',
                    function ($attribute) {
                        if ($this->user === null || !Password::validate($this->password, $this->user->password_hash)) {
                            $this->addError($attribute, Yii::t('user', 'Invalid login or password'));
                        }
                    }
                ]
            ]);
        }

        return $rules;
    }

    /**
     * Validates if the hash of the given password is identical to the saved hash in the database.
     * It will always succeed if the module is in DEBUG mode.
     *
     * @param $attribute
     * @return void
     */
    public function validatePassword($attribute)
    {
        if ($this->user === null || !Password::validate($this->password, $this->user->password_hash)) {
          $this->addError($attribute, Yii::t('user', 'Invalid login or password'));
        }
    }

    /**
     * Validates form and logs the user in.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $this->user->updateAttributes(['last_login_at' => new Expression('NOW()')]);
            return Yii::$app->getUser()->login($this->user, $this->rememberMe ? $this->module->rememberFor : 0);
        }

        return false;
    }


    /** @inheritdoc */
    public function formName()
    {
        return 'login-form';
    }

    /** @inheritdoc */
    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $this->user = $this->UserFinder->findUserByUsernameOrEmail(trim($this->login));

            return true;
        } else {
            return false;
        }
    }
}
