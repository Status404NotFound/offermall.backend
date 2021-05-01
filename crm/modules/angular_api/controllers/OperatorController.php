<?php

namespace crm\modules\angular_api\controllers;

use yii\base\Exception;
use yii\rest\ActiveController;
use yii\filters\auth\CompositeAuth;
use common\filters\auth\HttpBearerAuth;
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

    public function actionIndex()
    {

    }

    public function actionOptions($id = null)
    {
        return "ok";
    }

    public function actionTakeLead()
    {
        if (Yii::$app->request->isPost) $request = Yii::$app->request->post();
        else throw new Exception('Not valid method!');

        if (isset($request['order_id'])) {
            return Yii::$app->operator->takeLead($request['order_id']);
        } else {
            throw new HttpException(422, 'No Order Id was received!');
        }
    }

    public function actionGenerateLead()
    {

        if (Yii::$app->request->isPost) $request = Yii::$app->request->post();
        else throw new Exception('Not valid method!');

        return Yii::$app->operator->generateLead();
    }

    public function actionDetachLead()
    {
        if (Yii::$app->request->isPost) $request = Yii::$app->request->post();
        else throw new Exception('Not valid method!');

        if (isset($request['order_id'])) {
            return Yii::$app->operator->detachLead($request['order_id']);
        } else {
            throw new HttpException(422, 'No Order Id was received!');
        }
    }

    public function actionMakeCall()
    {

        if (Yii::$app->request->isPost) $request = Yii::$app->request->post();
        else throw new Exception('Not valid method!');

        $params = [
            'order_id' => $request['order_id'],
            'phone_extension' => $request['phone_extension'],
            'phone_regional' => $request['phone_regional'],
            'order_hash' => $request['order_hash'],
        ];

        Yii::$app->cc_api->makeCall($params);
    }
}