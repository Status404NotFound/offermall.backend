<?php

namespace common\services\log\orderInfo;

use common\helpers\FishHelper;
use common\models\customer\Customer;
use common\models\log\orderInfo\OrderInfoInstrument;
use common\models\log\orderInfo\OrderInfoLog;
use common\models\order\Order;
use common\models\order\OrderData;
use common\models\order\OrderStatus;
use common\models\order\StatusReason;
use common\services\ValidateException;
use yii\base\Exception;
use yii\db\ActiveRecord;
use Yii;

class OrderInfoLogService
{
    private $log;
    private $changed_attributes;
    private $order_id;

    public function logModel(ActiveRecord $model, $oldModel = null, $onDelete = false)
    {
        $modelName = $model->tableName();
        $modelClass = $model::className();

        $log = new OrderInfoLog();
        if ($modelName == 'customer') {
            /** @var Customer $model */
            $log->order_id = $model->getLastOrderId();
            $order = Order::findOne(['order_id' => $log->order_id]);
            $log->order_data_id = $order->orderData->order_data_id;
            $log->customer_id = $model->customer_id;
        } elseif ($modelName == 'order') {
            /** @var Order $model */
            $log->order_id = $model->order_id;
            $log->order_data_id = $model->orderData->order_data_id;
            $log->customer_id = $model->customer_id;
        } else {
            /** @var OrderData $model */
            $log->order_id = $model->order_id;
            $log->order_data_id = $model->order_data_id;
            $log->customer_id = $model->order->customer_id;
        }
        $this->order_id = $log->order_id;
        $log->instrument = (int)$model->getInstrument();

        if (Yii::$app->user->isGuest) {
            $log->user_id = NULL;
        } else {
            $log->user_id = Yii::$app->user->identity->getId();
        }

        $log->ip = $_SERVER['REMOTE_ADDR'];
        $log->city = $_SERVER['REMOTE_ADDR'];

        $log->model = $modelName;
        $log->datetime = date('Y-m-d H:i:s');

        $changed_attributes = $this->getChangedAttributes($model, $oldModel);
        foreach ($changed_attributes as $name => $value) {
            try {
                $this->log = clone $log;
                if ($this->log->model == 'customer') {
                }
                $this->log->field_name = $name;
                $this->log->last_value = isset($oldModel->$name) ? (string)$oldModel->$name : '';
                $this->log->new_value = (string)$value;
                $this->log->comment = $this->getUpdateComment($model, $oldModel);
                if (!$this->log->save())
                    throw new ValidateException($this->log->errors);
            } catch (ValidateException $e) {
                FishHelper::debug($e, 0, 0);
                continue;
            } catch (Exception $e) {
                FishHelper::debug($e, 0, 0);
                continue;
            }
        }
        return true;
    }

    private function getUpdateComment(ActiveRecord $model, ActiveRecord $oldModel)
    {
        if ($this->log->field_name == 'comment') {
            $comment = $this->log->new_value;
        } elseif ($this->log->field_name == 'order_status') {
            $comment = $this->getBaseComment() . ' change ' . '<strong>' . $this->log->field_name . '</strong>' . ' from ' . '<span style="color: #BF360C">' . OrderStatus::attributeLabels($this->log->last_value) . '</span>' . ' to ' . '<span style="color: #1A237E">' . OrderStatus::attributeLabels($this->log->new_value) . '</span>' . '.';
        } elseif ($this->log->field_name == 'status_reason') {
            $comment = $this->getBaseComment() . ' change ' . '<strong>' . $this->log->field_name . '</strong>' . ' from ' . '<span style="color: #BF360C">' . StatusReason::getReason($model->order_status, $this->log->last_value) . '</span>' . ' to ' . '<span style="color: #1A237E">' . StatusReason::getReason($model->order_status, $this->log->new_value) . '</span>' . '.';
        } else {
            $comment = $this->getBaseComment() . ' change ' . '<strong>' . $this->log->field_name . '</strong>' . ' from ' . '<span style="color: #BF360C">' . $this->log->last_value . '</span>' . ' to ' . '<span style="color: #1A237E">' . $this->log->new_value . '</span>' . '.';
        }
        return $comment;
    }

    private function getAddToCallListComment(ActiveRecord $model, ActiveRecord $oldModel, $operator_id)
    {
        if ($this->log->field_name == 'comment') {
            $comment = $this->log->new_value;
        } elseif ($this->log->field_name == 'order_status') {
            $comment = $this->getBaseComment() . ' change ' . '<strong>' . $this->log->field_name . '</strong>' . ' from ' . '<span style="color: #BF360C">' . OrderStatus::attributeLabels($this->log->last_value) . '</span>' . ' to ' . '<span style="color: #1A237E">' . OrderStatus::attributeLabels($this->log->new_value) . '</span>' . '.';
        } else {
            $comment = $this->getBaseComment() . ' change ' . '<strong>' . $this->log->field_name . '</strong>' . ' from ' . '<span style="color: #BF360C">' . $this->log->last_value . '</span>' . ' to ' . '<span style="color: #1A237E">' . $this->log->new_value . '</span>' . '.';
        }
        return $comment;
    }

    private function getBaseComment()
    {
        if (Yii::$app->user->isGuest) {
            $username = ' _ ';
        } else {
            $username = Yii::$app->user->identity->username;
        }
        return '<span style="color: #BDBDBD">' . $this->log->datetime . ' | ' . OrderInfoInstrument::instruments($this->log->instrument) . ' | ' . $this->log->ip . ': ' . '</span>'
            . '<strong>' . $username . '</strong>';
    }

    private function getChangedAttributes(ActiveRecord $model, ActiveRecord $oldModel)
    {
        $old_attributes = $oldModel->attributes;
        $changed_attributes = [];
        foreach ($model->attributes as $name => $value) {
            if ($name == 'updated_at' || $name == 'updated_by' ||
                $name == 'usd_total_cost' || $name == 'total_cost' || $name == 'total_amount' ||
                $name == 'advert_commission' || $name == 'wm_commission') continue;

            if (is_float($old_attributes[$name])) {
                $value = round($value, 3);
                $old_attributes[$name] = round($old_attributes[$name], 3);
            } elseif (is_integer($old_attributes[$name])) {
                $value = (integer)$value;
            }

            if ($value !== $old_attributes[$name]) {
                $changed_attributes[$name] = $value;
            }
        }
        return $changed_attributes;
    }
}