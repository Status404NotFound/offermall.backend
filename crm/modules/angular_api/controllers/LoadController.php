<?php

namespace crm\modules\angular_api\controllers;

use Yii;
use common\models\steal\StealDataSent;
use common\services\steal\StealDataService;
use common\services\ValidateException;
use crm\services\webmaster\exceptions\StealNotFoundException;
use common\services\order\logic\status\ChangeStatusException;
use yii\base\Exception;

class LoadController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'crm\models\steal\StealDataSent';

    /**
     * @var StealDataService
     */
    private $stealDataService;

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

    /**
     * @param $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * LoadController constructor.
     * @param string $id
     * @param \yii\base\Module $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->stealDataService = new StealDataService();
    }

    public function actionReadStealLog()
    {
        $sort_field = Yii::$app->request->getBodyParam('sortField');
        $result = $this->stealDataService->getStealLog($this->getRequestFilters(), $this->getRequestPagination(),
            $this->getRequestSortOrder(), $sort_field);
        $this->response->data = $result['result'];
        $this->setPaginationHeaders($result['count']['count_all']);
        $this->response->send();
    }

    public function actionReadFormLog()
    {
        $sort_field = Yii::$app->request->getBodyParam('sortField');
        $result = $this->stealDataService->readFormLog($this->getRequestFilters(), $this->getRequestPagination(),
            $this->getRequestSortOrder(), $sort_field);
        $this->response->data = isset($result['result']) ? $result['result'] : [];
        $this->setPaginationHeaders(isset($result['count']['count_all']) ? $result['count']['count_all'] : 0);
        $this->response->send();
    }

    public function actionStatus()
    {
        $request = Yii::$app->request->post();
        $id = $request['site_id'];
        $status = $request['status'];
        try {
            if (!$offer = StealDataSent::findOne(['site_id' => $id]))
                throw new StealNotFoundException('Site is not found.');
            $this->stealDataService->changeStatus($id, $status);
        } catch (ChangeStatusException $e) {
            $this->response->data['failed'] = $e->getMessage();
        } catch (ValidateException $e) {
            $this->response->data['failed'] = $e->getMessages();
        } catch (Exception $e) {
            $this->response->data['failed'] = $e->getMessage();
        }
        $this->response->send();
    }
}