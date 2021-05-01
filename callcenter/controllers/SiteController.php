<?php

    namespace callcenter\controllers;

    use common\models\callcenter\OperatorConf;
    use common\models\order\Order;
    use Yii;
    use yii\rest\Controller;
    use yii\base\Response;

    class SiteController extends Controller
    {

        public function actions()
        {
            return [
                'error' => [
                    'class' => 'yii\web\ErrorAction',
                ],
            ];
        }

        public function actionPing()
        {
            $response = new Response();
            $response->statusCode = 200;
            $response->data = Yii::t('app','ping');

            return $response;
        }


        public function actionError() {

            $response = new Response();
            $response->statusCode = 400;
            $response->data = json_encode([
                "name"      => "Bad Request",
                "message"   => Yii::t('app', 'The system could not process your request. Please check and try again.'),
                "code"      => 0,
                "status"    => 400,
                "type"      => "yii\\web\\BadRequestHttpException"
            ]);

            return $response;
        }

        public function actionIndex(){
            return 'ok';
        }

        public function actionAutoModeCard($order_id)
        {
//            Yii::$app->cc_api->
        }


    }
