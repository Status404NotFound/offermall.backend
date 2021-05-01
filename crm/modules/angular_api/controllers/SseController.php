<?php
namespace crm\modules\angular_api\controllers;

use common\models\DataList;
use crm\services\user\UserService;
use Yii;
use yii\web\Controller;
use yii\filters\auth\CompositeAuth;
use crm\services\events\MessageEventHandler;
use common\filters\auth\HttpBearerAuth;

class SseController extends Controller
{
    /**
     * @var string
     */
    public $modelClass = 'common\modules\user\models\tables\User';

    /**
     * SseController constructor.
     * @param $id
     * @param $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
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
            'only' => ['sse'],
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

    public function actionSse()
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