<?php

namespace regorder\controllers;

use yii\web\Controller;
use \yii\filters\Cors;
use \yii\filters\VerbFilter;

/**
 * Behavior controller
 */
class BehaviorController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['corsFilter'] = [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['POST', 'GET'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 3600,                 // Cache (seconds)
            ],
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'register' => ['post'],
                'my-land-postback' => ['get'],
            ],
        ];

        return $behaviors;
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }
}