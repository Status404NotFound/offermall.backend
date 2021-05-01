<?php

namespace common\services\order;

use common\models\log\orderInfo\OrderInfoLog;
use common\models\log\orderSku\OrderSkuLog;
use common\models\order\Order;
use Yii;

class OrderCommentService extends OrderCommonService
{
    public function getComments(Order $order)
    {
        $orderInfoLog_query = OrderInfoLog::find()->select('comment, datetime')
            ->where(['order_id' => $order->order_id]);
        if (!Yii::$app->user->isGuest && Yii::$app->user->identity->role != \common\modules\user\models\tables\User::ROLE_ADMIN)
        {
            $orderInfoLog_query->andWhere([
                'not in',
                'field_name',
                [
                    'usd_total_cost',
                    'advert_commission',
                    'usd_advert_commission',
                    'wm_commission',
                    'usd_wm_commission',
                    'bitrix_flag',
                    'flow_id',
                ]
            ]);
        }
        $orderInfoLog = $orderInfoLog_query->orderBy(['datetime' => SORT_DESC])
            ->asArray()
            ->all();

        $orderSkuLog = OrderSkuLog::find()->select('comment, datetime')
            ->where(['order_id' => $order->order_id])
            ->orderBy(['datetime' => SORT_DESC])
            ->asArray()
            ->all();

        $result = [];
        foreach ($orderInfoLog as $key => $comment) {
            $timestamp = $this->findEmptyKey($result, strtotime($comment['datetime']));
            $result[$timestamp] = $comment['comment'];
        }
        foreach ($orderSkuLog as $key => $comment) {
            $timestamp = $this->findEmptyKey($result, strtotime($comment['datetime']));
            $result[$timestamp] = $comment['comment'];
        }
        sort($result);
        return $result;
    }

    private function findEmptyKey($result, $timestamp)
    {
        if (isset($result[$timestamp])) {
            $timestamp++;
            $this->findEmptyKey($result, $timestamp);
        }
        return $timestamp;
    }
}