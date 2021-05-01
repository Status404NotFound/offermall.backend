<?php

namespace webmaster\modules\wm_api\controllers;

use common\modules\user\models\tables\User;
use common\services\webmaster\DomainParkingSrv;
use webmaster\models\form\Form;
use Yii;

/**
 * Class StatisticsController
 * @package webmaster\modules\wm_api\controllers
 */
class TestController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'webmaster\models\form\Form';

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
            'generate-form' => ['post'],
        ]);
        return $behaviors;
    }

    public function actionCreate()
    {
        $data = Yii::$app->request->post();
        $name = $data['name'];
        $domain = $data['domain'];
        $geo = $data['geo'];
        $landing = $data['landing'];
        $domainModel = new DomainParking();
        $response = Yii::$app->response;
        $response->data = [

        ];
        $response->send();
    }

    public static function getUser()
    {
        $auth_header = Yii::$app->request->headers['authorization'];
        $token = substr_replace($auth_header, '',0, 7);
        return User::findIdentityByAccessToken($token);
    }

    public static function getFormScript()
    {
        return '<div class="form-wrapper"></div><script src="form.js"></script> <script>formInit(725);</script>';
    }
}