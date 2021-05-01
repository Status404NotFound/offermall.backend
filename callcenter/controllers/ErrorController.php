<?php

namespace callcenter\controllers;

use yii\base\Controller;
use Yii;

class ErrorController extends Controller
{

    public function actionIndex()
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            return $this->render('error', ['exception' => $exception]);
        }
    }
}