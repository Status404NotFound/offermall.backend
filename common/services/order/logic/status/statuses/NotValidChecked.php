<?php

namespace common\services\order\logic\status\statuses;

use common\helpers\FishHelper;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\modules\user\models\tables\User;
use common\services\order\logic\status\ChangeStatusException;
use common\services\order\logic\status\ChangeStatusInterface;
use common\services\ValidateException;

class NotValidChecked extends BaseStatus implements ChangeStatusInterface
{
    public function init(Order $order, $params = [])
    {
        if (\Yii::$app->user->identity->role !== User::ROLE_ADMIN
            && \Yii::$app->user->identity->role !== User::ROLE_FIN_MANAGER
            && \Yii::$app->user->identity->getId() != 202) {
            throw new ChangeStatusException('Have no permission.');
        }

//        if (isset($params['regorder']) && $params['regorder'] == 1) {
//            if (\Yii::$app->user->identity->role !== User::ROLE_ADMIN
//                && \Yii::$app->user->identity->role !== User::ROLE_FIN_MANAGER
//                && \Yii::$app->user->identity->getId() != 202) {
//                throw new ChangeStatusException('Have no permission.');
//            }
//        }
//
        $this->status = OrderStatus::NOT_VALID_CHECKED;
        $this->order = $order;

        $this->checkTargets();
        $this->changeStatus();
        $this->wmPostback('url_cancelled');
        return true;
    }

    private function changeStatus()
    {
        $this->order->order_status = $this->status;
        if (!$this->order->save()) throw new ValidateException($this->order->errors);
    }
}