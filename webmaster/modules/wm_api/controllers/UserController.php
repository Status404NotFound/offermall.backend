<?php

namespace webmaster\modules\wm_api\controllers;

use common\modules\user\helpers\Password;
use Yii;
use yii\rest\ActiveController;
use yii\helpers\Url;

use yii\data\ActiveDataProvider;
use yii\filters\{
    auth\CompositeAuth, VerbFilter
};
use yii\web\{
    HttpException, NotFoundHttpException, ServerErrorHttpException
};
use common\modules\user\models\{
    tables\User,
    forms\LoginForm,
    forms\PasswordResetForm,
    forms\PasswordResetRequestForm,
    forms\PasswordResetTokenVerificationForm
};
use common\modules\user\models\Permission;
use common\filters\auth\HttpBearerAuth;

class UserController extends ActiveController
{
    public $modelClass = 'common\modules\User\models\tables\User';

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function actions()
    {
        return [];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
            ],

        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'index' => ['get'],
                'view' => ['get'],
                'create' => ['post'],
                'update' => ['put'],
                'delete' => ['delete'],
                'login' => ['post'],
                'reg-new' => ['post']
            ],
        ];

        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options', 'login', 'signup', 'confirm', 'reg-new', 'password-reset-request', 'password-reset-token-verification', 'password-reset'];
        return $behaviors;
    }

    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => User::find()
        ]);
    }

    public function actionView($id)
    {
        $staff = User::find()->where([
            'id' => $id
        ])->one();
        if ($staff) {
            return $staff;
        } else {
            throw new NotFoundHttpException("Object not found: $id");
        }
    }

    public function actionCreate()
    {
        $model = new User();
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->validate() && $model->save()) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($model->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute([$id], true));
        } else {
            // Validation error
            throw new HttpException(422, json_encode($model->errors));
        }

        return $model;
    }

    public function actionRegNew(){
        $request = Yii::$app->request;
        $data = $request->post();

        $usernameExist = User::existUsername($data['username']);
        $emailExist = User::existEmail($data['email']);

        $response = Yii::$app->response;

        if($usernameExist) {
            $response->statusCode = 405;
            $response->data = [
                'message' => 'Username already exist'
            ];
        } elseif($emailExist) {
            $response->statusCode = 405;
            $response->data = [
                'message' => 'Email already exist'
            ];
        } else {
            $userModel = new User();
            $userModel->password = $data['password'];
            $userModel->setAttributes([
                'email' => $data['email'],
                'username' => $data['username'],
                'role' => $data['role'],
                'flags' => 0
            ]);
            $userModel->save();
        }
        $response->send();
    }

    public function actionUpdate($id)
    {
        $model = $this->actionView($id);

        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->validate() && $model->save()) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);
        } else {
            // Validation error
            throw new HttpException(422, json_encode($model->errors));
        }

        return $model;
    }

    public function actionDelete($id)
    {
        $model = $this->actionView($id);

        //$model->status = User::STATUS_DELETED;

        if ($model->save(false) === false) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }

        $response = \Yii::$app->getResponse();
        $response->setStatusCode(204);
        return "ok";
    }

    public function actionLogin()
    {
        $model = new LoginForm();;

        $request = Yii::$app->request->post();
        $data = $request['LoginForm'];
        if ($model->load($request) && $model->login()) {
            $user = $model->getUser();

            if ($user->role != User::ROLE_WEBMASTER && $user->role != User::ROLE_SUPER_WM) {
                $user->auth_key = null;
                $user->update(['auth_token']);
                throw new HttpException(422, json_encode(['password' => ['Incorrect user name or password']]));
            }

            $user->generateAccessTokenAfterUpdatingClientInfo(true);

            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);
            $id = implode(',', array_values($user->getPrimaryKey(true)));

            $responseData = [
                'access_token' => $user->access_token,
            ];

            return $responseData;
        } else {
            $user = User::find()->where(['username' => $data['username']])->one();
            if (!isset($user))
            {
                throw new HttpException(422, "Error. Username didn't exist");
            } elseif ($user->getIsBlocked()) {
                throw new HttpException(422, "Error. You are blocked");
            } else {
                throw new HttpException(422, "Error. Please check your inputs");
            }
        }
    }

    public function actionPasswordResetRequest()
    {
        $model = new PasswordResetRequestForm();

        $model->load(Yii::$app->request->post());
        if ($model->validate() && $model->sendPasswordResetEmail()) {

            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);

            $responseData = "true";

            return $responseData;
        } else {
            // Validation error
            throw new HttpException(422, json_encode($model->errors));
        }
    }

    public function actionPasswordResetTokenVerification()
    {
        $model = new PasswordResetTokenVerificationForm();

        $model->load(Yii::$app->request->post());
        if ($model->validate() && $model->validate()) {

            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);

            $responseData = "true";

            return $responseData;
        } else {
            // Validation error
            throw new HttpException(422, json_encode($model->errors));
        }
    }

    /**
     * Resets password.
     */
    public function actionPasswordReset()
    {
        $model = new PasswordResetForm();
        $model->load(Yii::$app->request->post());

        if ($model->validate() && $model->resetPassword()) {

            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);

            $responseData = "true";

            return $responseData;
        } else {
            // Validation error
            throw new HttpException(422, json_encode($model->errors));
        }
    }

    public function actionOptions($id = null)
    {
        return "ok";
    }

    public function actionPermission()
    {
        $permission = new Permission(Yii::$app->user->identity->role, Yii::$app->user->identity->id);
        $response = Yii::$app->response;
        $response->data = [
            'role' => User::rolesIndexed()[Yii::$app->user->identity->role],
            'permissions' => $permission->getPermissionStringList(),
        ];
        $response->send();

    }

    public function actionSettings()
    {
        return [
            [
                'key' => 'timezone',
                'value' => Yii::$app->user->identity->profile->timezone,
            ],
        ];
    }
}
