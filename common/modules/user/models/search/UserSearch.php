<?php


namespace common\modules\user\models\search;

use common\modules\user\UserFinder;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * UserSearch represents the model behind the search form about User.
 */
class UserSearch extends Model
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
     * @param array  $config
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
            'fieldsSafe'        => [['username', 'email', 'created_at', 'last_login_at'], 'safe'],
            'createdDefault'    => ['created_at', 'default', 'value' => null],
            'lastloginDefault'  => ['last_login_at', 'default', 'value' => null],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'username'        => Yii::t('user', 'Username'),
            'email'           => Yii::t('user', 'Email'),
            'last_login_at'   => Yii::t('user', 'Last login'),
        ];
    }

    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = $this->UserFinder->getUserQuery()->with([
            'children' => function (ActiveQuery $query) {
                    $query->select(['id, {{%user}}.username as children_name']);
                },
            'parent' => function (ActiveQuery $query) {
                $query->select(['id, {{%user}}.username as parent_name']);
            },
            'profile'
        ]);

//        echo '<pre>' . print_r($query->createCommand()->sql, true) . '</pre>';
//        Yii::$app->end();


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'email', $this->email]);

        return $dataProvider;
    }
}
