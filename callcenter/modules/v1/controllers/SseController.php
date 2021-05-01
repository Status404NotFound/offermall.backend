<?php
/**
 * Created by PhpStorm.
 * User: ihor
 * Date: 26.06.17
 * Time: 11:42
 */

namespace callcenter\modules\v1\controllers;

use common\services\callcenter\call_list\CallListService;
use yii\base\Exception;
use yii\rest\ActiveController;
use yii\filters\auth\CompositeAuth;
use callcenter\filters\auth\HttpBearerAuth;
use yii\web\Controller;
use Yii;

use callcenter\services\events\MessageEventHandler;
use callcenter\services\events\DeleteEventHandler;

class SseController extends Controller
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

        $behaviors['contentNegotiator'] = [
            'class' => \yii\filters\ContentNegotiator::className(),
            'only' => ['message'],
            'formatParam' => '_format',
            'formats' => [
                'text/event-stream' => \yii\web\Response::FORMAT_RAW,
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

    public function actionMessage()
    {
        $sse = Yii::$app->sse;
        $sse->set('allow_cors', true);
        $sse->set('keep_alive_time', 6000);
        $sse->addEventListener('message', new MessageEventHandler());
        $sse->start();
        $sse->flush();
    }

    public function actionOptions($id = null)
    {
        return "ok";
    }

}