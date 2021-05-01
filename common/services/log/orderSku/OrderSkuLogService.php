<?php

namespace common\services\log\orderSku;

use common\helpers\FishHelper;
use common\models\log\orderSku\OrderSkuInstrument;
use common\models\log\orderSku\OrderSkuLog;
use common\models\log\orderSku\OrderSkuLogException;
use common\models\order\Order;
use common\models\order\OrderSku;
use common\services\order\OrderNotFoundException;
use Yii;
use yii\db\ActiveRecord;

class OrderSkuLogService
{
    private $log;

    public function logModel(ActiveRecord $model, $oldModel = null, $onDelete = false)
    {
        /**
         * @var OrderSku $model
         * @var OrderSku $oldModel
         */
        $this->log = new OrderSkuLog();

        $this->log->order_id = $model->order_id;
        $this->log->sku_id = $model->sku_id;
        $this->log->instrument = $model->getInstrument();

        $this->log->new_amount = $model->amount;

        if (Yii::$app->user->isGuest) {
            $this->log->user_id = NULL;
        } else {
            $this->log->user_id = Yii::$app->user->identity->getId();
        }

        $this->log->ip = $_SERVER['REMOTE_ADDR'];
        $this->log->city = $_SERVER['REMOTE_ADDR'];
        $this->log->datetime = date('Y-m-d H:i:s');

        if ($model->getIsNewRecord()) {
            $this->log->last_amount = null;
            $this->log->order_sku_id = OrderSku::find()->max('order_sku_id') + 1;
            $this->log->comment = $this->getInsertComment($model);
        } else {
            $this->log->last_amount = $oldModel->amount;
            $this->log->order_sku_id = $model->order_sku_id;
            $this->log->comment = $this->getUpdateComment($model, $oldModel);
        }
        if ($onDelete) {
            $this->log->delete = 1;
            $this->log->new_amount = null;
            $this->log->last_amount = $oldModel->amount;
            $this->log->comment = $this->getDeleteComment($model);
        }

        if (!$this->log->save())
            FishHelper::debug($this->log->errors, 0);
//            throw new OrderSkuLogException($this->log->errors);
        return true;
    }

    private function getInsertComment(ActiveRecord $model)
    {
        /** @var OrderSku $model */
        // TODO: Get order_hash against order_id
        if (!$order = Order::findOne(['order_id' => $model->order_id])) throw new OrderNotFoundException();
        return $this->getBaseComment() . ' add ' . '<span style="color: #1A237E">' . $model->amount . '</span>' . ' new ' . '<strong>' . $model->sku->sku_alias . '</strong>' .
//            ' to Order #' . $order->order_hash .
            '.';
    }

    private function getUpdateComment(ActiveRecord $model, ActiveRecord $oldModel)
    {
        /**
         * @var OrderSku $model
         * @var OrderSku $oldModel
         */
        // TODO: Get order_hash against order_id
        if (!$order = Order::findOne(['order_id' => $model->order_id])) throw new OrderNotFoundException();
        return $this->getBaseComment() . ' change ' . '<strong>' . $model->sku->sku_alias . '</strong>' . ' from ' . '<span style="color: #BF360C">' . $oldModel->amount . '</span>' . ' to ' . '<span style="color: #1A237E">' . $model->amount
//            . ' in Order #' . $order->order_hash
            . '</span>' . '.';
    }

    private function getDeleteComment(ActiveRecord $model)
    {
        /** @var OrderSku $model */
        // TODO: Get order_hash against order_id
        if (!$order = Order::findOne(['order_id' => $model->order_id])) throw new OrderNotFoundException();
        return $this->getBaseComment() . ' remove ' . '<span style="color: #1A237E">' . $model->amount . '</span>' . ' ' . '<strong>' . $model->sku->sku_alias . '</strong>'
//            . ' from Order #' . $order->order_hash
            . '.';
    }

    private function getBaseComment()
    {
        if (Yii::$app->user->isGuest) {
            $username = ' _ ';
        } else {
            $username = Yii::$app->user->identity->username;
        }
        return '<span style="color: #BDBDBD">' . $this->log->datetime . ' | ' . OrderSkuInstrument::instruments($this->log->instrument) . ' | ' . $this->log->ip . ': ' . '</span>'
            . '<strong>' . $username . '</strong>';
    }
}