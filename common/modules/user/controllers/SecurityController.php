<?php


namespace common\modules\user\controllers;

use common\modules\user\UserFinder;
use common\modules\user\models\forms\LoginForm;
use common\modules\user\models\tables\User;
use common\modules\user\Module;
use common\modules\user\traits\AjaxValidationTrait;
use common\modules\user\traits\EventTrait;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Controller that manages user authentication process.
 *
 * @property Module $module
 *
 * @author makandy <makandy42@gmail.com>
 */
class SecurityController extends Controller
{
    use AjaxValidationTrait;
    use EventTrait;

    /**
     * Event is triggered before logging user in.
     * Triggered with \common\modules\user\events\FormEvent.
     */
    const EVENT_BEFORE_LOGIN = 'beforeLogin';

    /**
     * Event is triggered after logging user in.
     * Triggered with \common\modules\user\events\FormEvent.
     */
    const EVENT_AFTER_LOGIN = 'afterLogin';

    /**
     * Event is triggered before logging user out.
     * Triggered with \common\modules\user\events\UserEvent.
     */
    const EVENT_BEFORE_LOGOUT = 'beforeLogout';

    /**
     * Event is triggered after logging user out.
     * Triggered with \common\modules\user\events\UserEvent.
     */
    const EVENT_AFTER_LOGOUT = 'afterLogout';

    /** @var UserFinder */
    protected $UserFinder;

    /**
     * @param string $id
     * @param \yii\base\Module $module
     * @param UserFinder $UserFinder
     * @param array  $config
     */
    public function __construct($id, $module, UserFinder $UserFinder, $config = [])
    {
        $this->UserFinder = $UserFinder;
        parent::__construct($id, $module, $config);
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    ['allow' => true, 'actions' => ['login'], 'roles' => ['?']],
                    ['allow' => true, 'actions' => ['login', 'logout'], 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        \Yii::$app->layout =  'empty';
        return parent::actions();
    }

    /**
     * Displays the login page.
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            $this->goHome();
        }

        /** @var LoginForm $model */
        $model = \Yii::createObject(LoginForm::className());
        $event = $this->getFormEvent($model);

        $this->performAjaxValidation($model);

        $this->trigger(self::EVENT_BEFORE_LOGIN, $event);

        if ($model->load(\Yii::$app->getRequest()->post()) && $model->login()) {
            $this->trigger(self::EVENT_AFTER_LOGIN, $event);
            return $this->goBack();
        }

        return $this->render('login', [
            'model'  => $model,
            'module' => $this->module,
        ]);
    }

    /**
     * Logs the user out and then redirects to the homepage.
     *
     * @return Response
     */
    public function actionLogout()
    {
        /** @var User $user */
        $user = \Yii::$app->user->identity;
        $event = $this->getUserEvent($user);

        $this->trigger(self::EVENT_BEFORE_LOGOUT, $event);

        \Yii::$app->getUser()->logout();

        $this->trigger(self::EVENT_AFTER_LOGOUT, $event);

        return $this->goHome();
    }

}
