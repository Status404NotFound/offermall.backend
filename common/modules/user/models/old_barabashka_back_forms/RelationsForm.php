<?php


namespace common\modules\user\models\old_barabashka_back_forms;

use common\modules\user\models\tables\User;
use common\modules\user\models\tables\UserChild;
use common\modules\user\Module;
use common\modules\user\UserFinder;
use common\modules\user\traits\ModuleTrait;
use Yii;
use yii\base\Model;

/**
 * LoginForm get user's login and password, validates them and logs the user in. If user has been blocked, it adds
 * an error to login form.
 *
 * @property Module $module
 * @author makandy <makandy42@gmail.com>
 */
class RelationsForm extends Model {
    use ModuleTrait;

    /** @var \common\modules\user\models\tables\User */
    protected $parent;

    /** @var \common\modules\user\models\tables\User */
    protected $user;

    /** @var string username parent. */
    public $parent_name;

    /** @var UserFinder */
    protected $UserFinder;

    /**
     * @param UserFinder $UserFinder
     * @param array $config
     * @throws \Exception
     */
    public function __construct(UserFinder $UserFinder, $config = [])
    {
        $this->UserFinder = $UserFinder;
        parent::__construct($config);
    }

    public function init()
    {
        parent::init();
        if ( empty($this->user) ) {
            throw new\Exception('Required user model not found');
        }
        $this->parent = $this->user->getParent()->one();
        if ( !empty($this->parent) ) {
            $this->parent_name = $this->parent->username;
        }
    }

    public function setParent(User $parent  ) {
        $this->parent = $parent;
    }

    public function setUser(User $user) {
        $this->user = $user;
    }

    public function getParent() {
        return $this->parent;
    }

    public function getUser() {
        return $this->user;
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'parent_name' => Yii::t('user', 'Parent name')
        ];
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            'parentTrim' => ['parent_name', 'trim'],
            'parentValidate' => [
                'parent_name',
                function ($attribute) {
                    $this->parent = $this->UserFinder->findUserByUsername($this->parent_name);

                    if ($this->parent !== null) {
                        if (!$this->parent->getIsConfirmed()) {
                            $this->addError($attribute, Yii::t('user', 'Select parent need to confirm'));
                        }
                        if ($this->parent->getIsBlocked()) {
                            $this->addError($attribute, Yii::t('user', 'Select parent account has been blocked'));
                        }
                        return;
                    }
                    $this->addError($attribute, Yii::t('user', 'Select parent not found.'));
                }
            ],
        ];
    }

    public function usersList() {
        return User::getUserList();
    }

    public function setRelations() {
        $relation = new UserChild();

        if ($this->validate() && $relation->setRelation($this->parent, $this->user) ) {
            return true;
        }
        return false;
    }


    /** @inheritdoc */
    public function formName()
    {
        return 'relations-form';
    }

}
