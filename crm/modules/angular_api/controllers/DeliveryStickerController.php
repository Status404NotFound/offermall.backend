<?php

namespace crm\modules\angular_api\controllers;

use Yii;
use crm\services\delivery\DeliveryStickersService;
use common\services\delivery\DeliveryException;

class DeliveryStickerController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'crm\models\delivery\DeliveryStickers';

    /**
     * @var DeliveryStickersService
     */
    private $orderStickerService;

    /**
     * OrderSticker constructor.
     * @param $id
     * @param $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->orderStickerService = new DeliveryStickersService();
    }

    public function actionList()
    {
        $this->response->data = $this->orderStickerService->stickerList();
        $this->response->send();
    }

    public function actionCreate()
    {
        $request = Yii::$app->request->post();
        $this->response->data = $this->orderStickerService->createSticker($request);
        $this->response->send();
    }

    public function actionUpdate()
    {
        $request = Yii::$app->request->getBodyParams();
        $this->response->data = $this->orderStickerService->updateSticker($request);
        $this->response->send();
    }

    public function actionChangeStatus()
    {
        $sticker_id = Yii::$app->request->post('id');
        $this->response->data = $this->orderStickerService->changeStatus($sticker_id);
        $this->response->send();
    }

    public function actionDelete()
    {
        $sticker_id = Yii::$app->request->get('sticker_id');
        if (empty($sticker_id)) throw new DeliveryException('Sticker id must be set.');
        $this->response->data = $this->orderStickerService->deleteSticker($sticker_id);
        $this->response->send();
    }
}