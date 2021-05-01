<?php

namespace crm\services\user;

use Yii;
use common\modules\user\models\tables\User;
use common\modules\user\UserFinder;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;

/**
 * UserSearchForm represents the model behind the search form about User.
 */
class UserSearchForm extends Model
{
    /** @var string */
    public $username;

    /** @var string */
    public $email;

    /** @var int */
    public $created_at;

    /** @var int */
    public $last_login_at;

    /** @var string */
    public $registration_ip;

    /** @var UserFinder */
    protected $UserFinder;

    /**
     * @param UserFinder $UserFinder
     * @param array $config
     */
    public function __construct(UserFinder $UserFinder, $config = [])
    {
        $this->UserFinder = $UserFinder;
        parent::__construct($config);
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            'fieldsSafe' => [['username', 'email', 'created_at', 'last_login_at'], 'safe'],
            'createdDefault' => ['created_at', 'default', 'value' => null],
            'lastloginDefault' => ['last_login_at', 'default', 'value' => null],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('user', 'Username'),
            'email' => Yii::t('user', 'Email'),
            'last_login_at' => Yii::t('user', 'Last login')
        ];
    }

    /**
     * @param array $filters
     * @param null $pagination
     * @param null $sort_order
     * @param null $sort_field
     * @return array
     */
    public function search($filters = [], $pagination = null, $sort_order = null, $sort_field = null)
    {
        $query = $this->UserFinder->getUserQuery()->joinWith('parent')->alias('U');

        $this->UserFinder->getUserQuery()->with([
            'children' => function (ActiveQuery $query) {
                $query->select(['id, {{%user}}.username as children_name']);
            },
            'parent' => function (ActiveQuery $query) {
                $query->select(['id, {{%user}}.username as parent_name']);
            },
        ]);

        if (Yii::$app->user->identity->role !== User::ROLE_ADMIN) {
            $query->where(['in', 'parent', [Yii::$app->user->id, Yii::$app->user->identity->getOwnerId()]]);
        }
    
        $query->andWhere(['U.flags' => 0]);

        if (isset($filters['user_id'])) $query->andWhere(['U.id' => $filters['user_id']['value']]);
        if (isset($filters['role'])) $query->andWhere(['U.role' => $filters['role']['value']]);

        if (isset($filters['status']) && $filters['status']['value'] === 0) {
            $query->andWhere(['U.blocked_at' => null]);
        } elseif (isset($filters['status']) && $filters['status']['value'] === 1) {
            $query->andWhere(['not', ['U.blocked_at' => null]]);
        } else {
            $query->andWhere(['OR',
                ['NOT', ['U.blocked_at' => null]],
                ['U.blocked_at' => null],
            ]);
        }

        if (isset($filters['username'])) $query->andFilterWhere(['LIKE', 'U.username', $filters['username']['value']]);
        if (isset($filters['parent'])) $query->andFilterWhere(['LIKE', 'user.username', $filters['parent']['value']]);
        if (isset($filters['email'])) $query->andFilterWhere(['LIKE', 'U.email', $filters['email']['value']]);

        if (isset($filters['date'])) {
            $start = new \DateTime($filters['date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', 'U.last_login_at', $start_date]);
            $query->andWhere(['<', 'U.last_login_at', $end_date]);
        }

        $sort_order = ($sort_order == -1) ? SORT_DESC : SORT_ASC;
        if (isset($sort_field)) {
            $query->orderBy([$sort_field => $sort_order]);
        } else {
            $query->orderBy(['id' => SORT_DESC]);
        }

        $count = clone $query;
        $count_all = $count->count();

        if (isset($pagination)) $query->offset($pagination['first_row'])->limit($pagination['rows']);

        $result = [
            'result' => $query
                ->asArray()
                ->all(),
            'total' => $count_all
        ];

        foreach ($result['result'] as &$user) {

            unset($user['access_token']);
            unset($user['access_token_expired_at']);
            unset($user['auth_key']);
            unset($user['updated_at']);
            unset($user['unconfirmed_email']);
            unset($user['registration_ip']);
            unset($user['password_hash']);
            unset($user['created_at']);
            unset($user['confirmed_at']);
            
            $user['role'] = User::rolesIndexed()[$user['role']];
            $user['block'] = ($user['blocked_at'] != null) ? true : false;
            
            if (Yii::$app->user->identity->role === User::ROLE_ADMIN) {
                $user['is_editable'] = true;
            } else {
                $user['is_editable'] = false;
                foreach ($user['parent'] as $parent) {
                    if (Yii::$app->user->id === (int)$parent['id']) {
                        $user['is_editable'] = true;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param null $id
     * @return array
     * @throws UserServiceException
     */
    public function userInfo($id = null)
    {
        $query = $this->UserFinder->getUserQuery()->joinWith(['parent' => function (ActiveQuery $a) {
            $a->select(['id', 'username']);
        }, 'profile'])->alias('U');

        if (!is_null(Yii::$app->user->identity->getOwnerId())) {
            $query->where(['parent' => Yii::$app->user->identity->getOwnerId()]);
            $query->andWhere(['U.id' => $id]);
        } else {
            $query->andWhere(['U.id' => $id]);
        }

        $user = $query
            ->asArray()
            ->one();

        if (!$user)
            throw new UserServiceException('Access denied.');

        unset($user['password_hash']);
        unset($user['auth_key']);
        unset($user['access_token']);
        unset($user['unconfirmed_email']);
        unset($user['registration_ip']);
        unset($user['access_token_expired_at']);

        $user['avatar'] = $user['profile']['avatar'] ?? '';
        $user['role_id'] = $user['role'];
        $user['role'] = User::rolesIndexed()[$user['role']];
        $user['parent'] = ArrayHelper::getColumn($user['parent'], 'id');
        $user['online'] = (string)(new User())->getIsOnline($user['id']);
        $user['confirmed_at'] = ($user['confirmed_at'] != null) ? $user['confirmed_at'] = 'Confirmed at ' . $user['confirmed_at'] : $user['confirmed_at'] = 'Unconfirmed';
        $user['blocked_at'] = ($user['blocked_at'] != null) ? $user['blocked_at'] = 'Blocked at ' . $user['blocked_at'] : $user['blocked_at'] = 'Not blocked';
        $user['created_at'] = 'Registered at ' . $user['created_at'];
        $user['updated_at'] = 'Updated at ' . $user['updated_at'];

        return $user;
    }

    /**
     * @return array
     */
    public function profileInfo()
    {
        $query = $this->UserFinder->getUserQuery()->joinWith('profile');

        $user_profile = $query
            ->where(['user.id' => Yii::$app->user->identity->getId()])
            ->asArray()
            ->one();

        return [
            'id' => $user_profile['id'],
            'username' => $user_profile['username'],
            'name' => $user_profile['profile']['name'],
            'avatar' => $user_profile['profile']['avatar'],
            'phone_number' => $user_profile['profile']['phone_number'],
            'email' => $user_profile['email'],
            'location' => $user_profile['profile']['location'],
            'timezone' => $user_profile['profile']['timezone'],
            'audio' => $user_profile['profile']['notification_audio_name'],
            'created_at' => 'Registered at ' . $user_profile['created_at'],
        ];
    }
}