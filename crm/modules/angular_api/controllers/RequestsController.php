<?php

namespace crm\modules\angular_api\controllers;

use Yii;
use common\models\webmaster\WmOffer;
use crm\services\request\RequestsService;
use crm\services\webmaster\exceptions\OfferNotFoundException;
use crm\services\webmaster\exceptions\ChangeStatusException;
use common\services\ValidateException;
use yii\base\Exception;

class RequestsController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\webmaster\WmOffer';

    /**
     * @var RequestsService
     */
    private $requestService;

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
     * RequestsController constructor.
     * @param string $id
     * @param \yii\base\Module $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->requestService = new RequestsService();
    }

    public function actionRequestsList()
    {
        $pagination = [];
        $sort_order = Yii::$app->request->getBodyParam('sortOrder');
        $pagination['first_row'] = Yii::$app->request->getBodyParam('firstRow');
        $pagination['rows'] = Yii::$app->request->getBodyParam('rows');
        $result = $this->requestService->requestList($pagination, $sort_order);
        $this->response->data = $result['result'];
        $this->response->headers->add('Access-Control-Expose-Headers', 'X-Pagination-Total-Count');
        $this->response->headers->add('X-Pagination-Total-Count', $result['total']);
        $this->response->send();
    }

    public function actionInfo($wm_offer_id)
    {
        $this->response->data = $this->requestService->getRequestView($wm_offer_id);
        $this->response->send();
    }

    public function actionConfirm()
    {
        $request = Yii::$app->request->getBodyParams();
        $ids = is_array($request['request_id']) ? $request['request_id'] : [$request['request_id']];
        $status = WmOffer::STATUS_TAKEN;
        foreach ($ids as $key => $id) {
            try {
                if (!$offer = WmOffer::findOne(['wm_offer_id' => $id]))
                    throw new OfferNotFoundException('Offer is not found.');
                $this->requestService->changeStatus($id, $status);
            } catch (ChangeStatusException $e) {
                $this->response->data['failed'][$id] = $e->getMessage();
                continue;
            } catch (ValidateException $e) {
                $this->response->data['failed'][$id] = $e->getMessages();
                continue;
            } catch (Exception $e) {
                $this->response->data['failed'][$id] = $e->getMessage();
                continue;
            }
        }
        $this->response->send();
    }

    public function actionReject()
    {
        $request = Yii::$app->request->getBodyParams();
        $ids = is_array($request['request_id']) ? $request['request_id'] : [$request['request_id']];
        $status = WmOffer::STATUS_REJECTED;
        foreach ($ids as $key => $id) {
            try {
                if (!$offer = WmOffer::findOne(['wm_offer_id' => $id]))
                    throw new OfferNotFoundException('Offer is not found.');
                $this->requestService->changeStatus($id, $status);
            } catch (ChangeStatusException $e) {
                $this->response->data['failed'][$id] = $e->getMessage();
                continue;
            } catch (ValidateException $e) {
                $this->response->data['failed'][$id] = $e->getMessages();
                continue;
            } catch (Exception $e) {
                $this->response->data['failed'][$id] = $e->getMessage();
                continue;
            }
        }
        $this->response->send();
    }
}