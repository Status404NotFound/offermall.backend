<?php

namespace crm\modules\angular_api\controllers;

use Yii;
use yii\base\Module;
use common\services\contact\ContactSearchService;


class ContactController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\order\Order';

    /**
     * @var ContactSearchService
     */
    private $contactSearchService;

    /**
     * ContactSearchController constructor.
     * @param $id
     * @param Module $module
     * @param array $config
     */
    public function __construct($id, Module $module, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->contactSearchService = new ContactSearchService();
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
        ]);
        return $behaviors;
    }

    public function actionSearch()
    {
        $sort_field = Yii::$app->request->getBodyParam('sortField');
        $sort_order = Yii::$app->request->getBodyParam('sortOrder');
        $result = $this->contactSearchService->searchContact($this->filters, $this->getRequestPagination(), $sort_field, $sort_order);
        $this->response->data = $result['result'];
        $this->setPaginationHeaders($result['count']['count_all']);
        $this->response->send();
    }
}