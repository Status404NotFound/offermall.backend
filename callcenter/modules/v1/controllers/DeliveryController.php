<?php
/**
 * Created by PhpStorm.
 * User: ihor
 * Date: 12.06.17
 * Time: 16:24
 */

namespace callcenter\modules\v1\controllers;

use common\services\callcenter\DeliveryService;
use Yii;
use yii\base\Exception;
use yii\rest\ActiveController;
use yii\filters\auth\CompositeAuth;
use callcenter\filters\auth\HttpBearerAuth;

class DeliveryController extends ActiveController
{
    public $modelClass = 'common\models\order\Order';

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

    public function actionList(){

        if (Yii::$app->request->isPost) $request = Yii::$app->request->post();
        else throw new Exception('Not valid method!');

        $response = Yii::$app->response;
        $delivery = new DeliveryService($request);
        $response->data = $delivery->list;


//        $delivery->order_status =
//            isset($request['filters']['order_status']['value'])
//            ? $request['filters']['order_status']['value']
//            : null;
//
//        $data = [];
//
//        if(!isset($request['type'])) $request['type'] = 'present';
//
//        /**
//         * Present
//         */
//
//        if ($request['type'] == 'present'){
//
//            if (!isset($delivery->order_status)){
//                $delivery->order_status = [3, 4];
//            }
//
//            $data = $delivery->getDeliveryList($request);
//        }
//
//        /**
//         * History
//         */
//
//        if ($request['type'] == 'history'){
//
//            if (!isset($delivery->order_status)){
//                $delivery->order_status = [4];
//            }
//
//            $data = $delivery->getDeliveryList($request);
//        }
//
//        /**
//         * Returned
//         */
//        if ($request['type'] == 'returned'){
//
//            if (!isset($delivery->order_status)){
//                $delivery->order_status = [3];
//            }
//
//            $data = $delivery->getDeliveryList($request);
//        }
//
//        /**
//         * Empty
//         */
//        if ($request['type'] == 'empty'){
//
//            if (!isset($delivery->order_status)){
//                $delivery->order_status = [3, 4];
//            }
//
//            $data = $delivery->getDeliveryList($request);
//        }


        $response->headers->add('Access-Control-Expose-Headers', 'X-Pagination-Total-Count');
        $response->headers->add('X-Pagination-Total-Count', $delivery->count);
        $response->send();

    }



    public function actionOptions($id = null)
    {
        return "ok";
    }

}