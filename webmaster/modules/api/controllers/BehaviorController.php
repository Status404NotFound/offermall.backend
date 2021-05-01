<?php

namespace webmaster\modules\api\controllers;

use Yii;
use yii\base\Module;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;
use yii\filters\auth\CompositeAuth;
use common\filters\auth\HttpBearerAuth;
use common\controllers\ActionResult;

class BehaviorController extends ActiveController
{
    public $response;
    public $result;
    public $referrer;

    public $pagination = [];
    public $filters = [];
    public $sort_order = SORT_DESC;

    /**
     * BehaviorController constructor.
     * @param $id
     * @param Module $module
     * @param array $config
     */
    public function __construct($id, Module $module, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->response = Yii::$app->getResponse();
        $this->result = new ActionResult();
        $this->referrer = self::setReferrer();
        $this->getRequestFilters();
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

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'index' => ['get'],
                'create' => ['post'],
                'view' => ['get'],
                'update' => ['put'],
                'delete' => ['delete'],
//                'login' => ['post'],
//                'update' => ['patch'],

            ],
        ];

        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => Cors::className(),
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

    public function getRequestPagination()
    {
        $this->pagination['first_row'] = Yii::$app->request->getBodyParam('firstRow');
        $this->pagination['rows'] = Yii::$app->request->getBodyParam('rows');
        return $this->pagination;
    }

    public function getRequestFilters()
    {
        $this->filters = (!empty(Yii::$app->request->getBodyParam('filters'))) ? Yii::$app->request->getBodyParam('filters') : [];
        return $this->filters;
    }

    public function getRequestSortOrder()
    {
        $this->sort_order = Yii::$app->request->getBodyParam('sortOrder');
        // TODO: Fix numbers of sort from back
        $this->sort_order = ($this->sort_order == 1) ? SORT_DESC : SORT_ASC;
        return $this->sort_order;
    }

    public function setPaginationHeaders($total_count)
    {
        $this->response->headers->add('Access-Control-Expose-Headers', 'X-Pagination-Total-Count');
        $this->response->headers->add('X-Pagination-Total-Count', $total_count);
    }

    public function actionOptions($id = null)
    {
        return "ok";
    }

    private function setReferrer()
    {
        return Yii::$app->request->referrer;
    }

    protected function getReferrer()
    {
        return $this->referrer;
    }

    protected function parseReferrer()
    {
        return parse_url(self::getReferrer())['path'];
    }
}