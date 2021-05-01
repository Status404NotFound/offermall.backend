<?php
/**
 * Created by PhpStorm.
 * User: ihor
 * Date: 11.08.17
 * Time: 17:04
 */

namespace callcenter\modules\v1\controllers;


use callcenter\filters\auth\HttpBearerAuth;
use common\models\geo\GeoRegion;
use common\services\callcenter\call_list\CustomerService;
use Yii;
use yii\base\Exception;
use yii\filters\auth\CompositeAuth;
use yii\rest\ActiveController;

class CustomerController extends ActiveController
{
    public $modelClass = 'common\models\customer\Customer';

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
        $behaviors['authenticator'] = ['class' => CompositeAuth::className(), 'authMethods' => [HttpBearerAuth::className(),],];
        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);
        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => ['Origin' => ['*'],
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

    public function actionSave()
    {
        $request = Yii::$app->request->post();
        $response = Yii::$app->response;
        $customerService = new CustomerService($request['customer_id']);
        $data = $customerService->update($request);
        $response->data = $data;
        $response->send();
    }

    public function actionGetCities()
    {
        if (Yii::$app->request->isGet) $request = Yii::$app->request->get();
        else throw new Exception('Not valid method!');
        $response = Yii::$app->response;
        $customerService = new CustomerService($request['customer_id']);
        $response->data = $customerService->availableCities();
        $response->send();
    }
    
    public function actionGetAreas(): void
    {
        $request = Yii::$app->request;
        
        if ( !$request->isPost) {
            throw new Exception('Not valid method!');
        }
        
        if ( !$customer_id = $request->post('customer_id')) {
            throw new Exception('Customer is required');
        }

        $region_id = $request->post('region_id') ?? null;
        Yii::$app->response->data = (new CustomerService($customer_id))->availableAreas($region_id);
        Yii::$app->response->send();
    }

    public function actionSaveAddress(): void
    {
        $request = Yii::$app->request;
    
        if ( !$request->isPost) {
            throw new Exception('Not valid method!');
        }
    
        if ( !$customer_id = $request->post('customer_id')) {
            throw new Exception('Customer is required');
        }
        
        if ( !$city_id = $request->post('city_id')) {
            throw new Exception('City is required');
        }
        
        $uae_regions = GeoRegion::find()->where(['geo_id' => 228])->asArray()->all();
        $area_id = $request->post('area_id');
        
        foreach ($uae_regions as $region) {
            if ( !$area_id && $region['region_id'] === $city_id) {
                throw new Exception('Area is required');
            }
        }
    
        if ( !$address = $request->post('address_input')) {
            throw new Exception('Address is required');
        }
        
        Yii::$app->response->data = (new CustomerService($customer_id))->saveAddress($city_id, $area_id, $address);
        Yii::$app->response->send();
    }

    public function actionHistory()
    {
        $response = Yii::$app->response;
        $request = Yii::$app->request->post();
        $customer_id = $request['customer_id'];
        $order_id = $request['order_id'];
        $customerSrv = new CustomerService($customer_id);
        $response->data = $customerSrv->getHistory($order_id);
        $response->send();

    }

    public function actionOptions($id = null)
    {
        return "ok";
    }

}