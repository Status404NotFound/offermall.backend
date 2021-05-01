<?php

namespace regorder\controllers;

use yii\web\Controller;

class ErrorController extends Controller
{

    public function actionIndex()
    {
        $exception = \Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            \Yii::error($exception, 'error');
            return $this->render('error', ['exception' => $exception]);
//        return $this->render('error');
        }
//
//        return true;
    }

    public function action404()
    {
        return $this->render('404');
    }
}