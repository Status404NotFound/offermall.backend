<?php


namespace webmaster\modules\api\controllers;


use Yii;

class BotController extends BehaviorController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $res = Yii::$app->telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => 'hello world!!'
        ]);

    }
}