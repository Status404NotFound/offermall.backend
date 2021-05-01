<?php

namespace crm\modules\angular_api\controllers;

use Yii;
use yii\rest\ActiveController;

use yii\filters\{
    auth\CompositeAuth, VerbFilter
};
use yii\web\{
    HttpException
};
use common\modules\user\models\{
    Permission, tables\User, forms\LoginForm, forms\PasswordResetForm,
    forms\PasswordResetRequestForm, forms\PasswordResetTokenVerificationForm
};
use common\filters\auth\HttpBearerAuth;
use crm\services\user\UserService;
use crm\services\user\UserSearchForm;

class UserController extends ActiveController
{
    /**
     * @var string
     */
    public $modelClass = 'common\modules\User\models\tables\User';

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var UserSearchForm|object
     */
    private $searchModel;

    /**
     * UserController constructor.
     * @param $id
     * @param $module
     * @param array $config
     * @throws \yii\base\InvalidConfigException
     */
    public function __construct($id, $module, $config = [])
    {
        $this->userService = new UserService();
        $this->searchModel = Yii::createObject(UserSearchForm::className());
        parent::__construct($id, $module, $config);
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [];
    }

    /**
     * @return array
     */
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
                'list' => ['post'],
                'advert-list' => ['get'],
                'wm-list' => ['get'],
                'view' => ['get'],
                'create' => ['post'],
                'update' => ['put'],
                'delete' => ['delete'],
                'login' => ['post']
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
        $behaviors['authenticator']['except'] = ['options', 'create', 'login', 'signup', 'confirm', 'password-reset-request', 'password-reset-token-verification', 'password-reset'];
        return $behaviors;
    }

    public function actionList()
    {
        $pagination = [];
        $response = Yii::$app->response;
        $filters = Yii::$app->request->getBodyParam('filters');
        $sort_order = Yii::$app->request->getBodyParam('sortOrder');
        $sort_field = Yii::$app->request->getBodyParam('sortField');
        $pagination['first_row'] = Yii::$app->request->getBodyParam('firstRow');
        $pagination['rows'] = Yii::$app->request->getBodyParam('rows');
        $result = $this->searchModel->search($filters, $pagination, $sort_order, $sort_field);
        $response->data = $result['result'];
        $response->headers->add('Access-Control-Expose-Headers', 'X-Pagination-Total-Count');
        $response->headers->add('X-Pagination-Total-Count', $result['total']);
        $response->send();
    }

    public function actionCreate()
    {
        $post = Yii::$app->request->post();
        $response = Yii::$app->response;
        $response->data = $this->userService->createUser($post);
        $response->send();
    }

    public function actionUpdate()
    {
        $request = Yii::$app->request->getBodyParams();
        $response = Yii::$app->response;
        $response->data = $this->userService->updateUser($request);
        $response->send();

    }

    public function actionPermissions()
    {
        $response = Yii::$app->response;
        $user_id = Yii::$app->request->post('user_id');
        $role_id = Yii::$app->request->post('role_id');
        $permission = (!$user = User::findOne($user_id)) ? new Permission($role_id) : new Permission($user->role, $user->id);
        $response->data = $permission->getListOfPermissions($role_id);
        $response->send();
    }

    public function actionRoles()
    {
        $response = Yii::$app->response;
        $roles = $this->userService->getRoleData();
        $response->data = $roles;
        $response->send();
    }

    public function actionUpdatePermissions()
    {
        $data = Yii::$app->request->post();
        $response = Yii::$app->response;
        $response->data = $this->userService->savePermissions($data);
        $response->send();
    }

    public function actionView()
    {
        $id = Yii::$app->request->get('id');
        $response = Yii::$app->response;
        $result = $this->searchModel->userInfo($id);
        $response->data = $result;
        $response->send();
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

    public function actionBlock()
    {
        $request = Yii::$app->request->getBodyParams();
        $response = Yii::$app->response;
        $response->data = $this->userService->blockUser($request);
        $response->send();
    }

    public function actionProfile()
    {
        $response = Yii::$app->response;
        $result = $this->searchModel->profileInfo();
        $response->data = $result;
        $response->send();
    }

    public function actionProfileSave()
    {
        $request = Yii::$app->request->getBodyParams();
        $response = Yii::$app->response;
        $response->data = $this->userService->saveProfile($request);
        $response->send();
    }

    public function actionDelete()
    {
        $response = Yii::$app->response;
        $id = Yii::$app->request->get('id');
        $response->data = $this->userService->deleteUser($id);
        $response->send();
    }

    public function actionLogin()
    {
        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $user = $model->getUser();
            if ($user->role == User::ROLE_WEBMASTER || $user->role == User::ROLE_OPERATOR) {
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
            // Validation error
            throw new HttpException(422, json_encode($model->errors));
        }
    }

    public function actionAvatar()
    {
        $response = Yii::$app->response;
        $response->data = $this->userService->getUserAvatar();
        $response->send();
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
