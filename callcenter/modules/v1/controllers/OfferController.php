<?php
/**
 * Created by PhpStorm.
 * User: ihor
 * Date: 19.04.17
 * Time: 14:17
 */

namespace callcenter\modules\v1\controllers;

use common\models\DataList;
use common\models\Instrument;
use common\models\log\CallListLog;
use common\models\offer\Offer;
use common\services\callcenter\call_list\CallListService;
use common\services\callcenter\OperatorSettingsSrv;
use yii\rest\ActiveController;
use yii\filters\auth\CompositeAuth;
use callcenter\filters\auth\HttpBearerAuth;
use Yii;

class OfferController extends ActiveController
{
    public $modelClass = 'common\models\offer\Offer';
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
        //return OperatorSettingsSrv::getOperatorOffers(Yii::$app->user->id, true, true);
    }

    public function actionList()
    {
        $response = Yii::$app->response;
        $response->data = (new DataList())->offers;
        $response->send();
    }

    public function actionCountryList()
    {
        $response = Yii::$app->response;
        $dataList = new DataList();
        $response->data = $dataList->getOffersGeo();
        $response->send();
    }

    public function actionGeo()
    {
        $response = Yii::$app->response;
        $offer_id = Yii::$app->request->get('offer_id');
        $dataList = new DataList();
        $response->data = $dataList->getOfferGeo($offer_id);
        $response->send();
    }

    public function actionOptions($id = null) {
        return "ok";
    }

}