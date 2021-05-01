<?php

namespace callcenter\modules\v1\controllers;

use callcenter\services\operator_activity\OperatorActivityService;
use common\models\callcenter\CallList;
use common\models\Instrument;
use common\modules\user\models\Permission;
use common\modules\user\models\tables\User;
use common\services\callcenter\call_list\CallListService;
use common\services\callcenter\OperatorSettingsSrv;
use common\services\log\LogSrv;
use crm\services\callcenter\OperatorDailySrv;
use yii\base\Exception;
use yii\rest\ActiveController;
use yii\filters\auth\CompositeAuth;
use callcenter\filters\auth\HttpBearerAuth;
use Yii;
use yii\web\HttpException;

class OperatorController extends ActiveController
{
    public $modelClass = 'common\models\callcenter\CallList';
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
        $behaviors['authenticator']['except'] = ['options'];


        return $behaviors;
    }

    public function actionIndex(){

    }

    public function actionTimeCheck()
    {
        $request = Yii::$app->request;
        $response = Yii::$app->response;
        $operatorActivitySrv = new OperatorActivityService();
        $response->data = $operatorActivitySrv->addActivity($request->post());
        $response->send();
    }

    public function actionOptions($id = null) {
        return "ok";
    }

    public function actionTakeLead(){

        if (Yii::$app->request->isPost) $request = Yii::$app->request->post();
        else throw new Exception('Not valid method!');

        if (isset($request['order_id'])){

            return Yii::$app->operator->takeLead($request['order_id']);
        }else{
            throw new HttpException(422, 'No Order Id was received!');
        }
    }


    public function actionToDoList()
    {

        if (Yii::$app->request->isGet) $request = Yii::$app->request->post();
        else throw new Exception('Not valid method!');

        $call_service = new CallListService();

        $list = $call_service->getToDoCalls();

        return $list;
    }



    public function actionCallList()
    {

        if (Yii::$app->request->isGet) $request = Yii::$app->request->post();
        else throw new Exception('Not valid method!');

        $call_service = new CallListService();

        $list = $call_service->getCallList();

        return $list;
    }



    public function actionPlanList()
    {

        if (Yii::$app->request->isGet) $request = Yii::$app->request->post();
        else throw new Exception('Not valid method!');

        $response = Yii::$app->response;

        $call_service = new CallListService();

        $response->data = $call_service->getPlanCalls();

        $response->send();
    }



    public function actionGenerateLead()
    {
        $response = Yii::$app->response;
        $response->data = Yii::$app->operator->generateLead();
//        $response->statusCode = 422;
        $response->send();
    }

    public function actionChangeStatus()
    {
        $response = Yii::$app->response;
        $request = Yii::$app->request->post();
        $status_id = $request['status_id'];
        $response->data = Yii::$app->operator->setOperatorStatus($status_id);
        $response->send();
    }

    public function actionPermission()
    {
        $permission = new Permission(Yii::$app->user->identity->role, Yii::$app->user->identity->id);
        $response = Yii::$app->response;
        $response->data = [
            'call_mode' => Yii::$app->operator->call_mode == OperatorSettingsSrv::MANUAL_MODE ? 'Manual' : 'Auto',
            'role' => User::rolesIndexed()[Yii::$app->user->identity->role],
            'permissions' => $permission->getPermissionStringList(),
        ];
        $response->send();
    }

    public function actionSetFine()
    {
        $response = Yii::$app->response;
        $response->data = Yii::$app->operator->setFine();
        $response->send();
    }

    public function actionStatuses()
    {
        $response = Yii::$app->response;
        $response->data = Yii::$app->operator->getStatuses();
        $response->send();
    }

    public function actionStatus()
    {
        $response = Yii::$app->response;
        $response->data = ['status' => Yii::$app->operator->status];
        $response->send();
    }

}