<?php

namespace webmaster\controllers;

use yii\base\Controller;

class ErrorController extends Controller
{

    public function actionIndex()
    {
//        return 'from ErrorController';
        return $this->render('404');
    }
}
