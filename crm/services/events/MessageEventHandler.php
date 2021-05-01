<?php
namespace crm\services\events;

use Yii;
use odannyc\Yii2SSE\SSEBase;
use common\modules\user\models\tables\BaseProfile;
use common\models\webmaster\WmOffer;
use common\models\webmaster\WmCheckout;
use common\models\steal\StealDataSent;
use common\models\order\OrderView;
use common\models\order\OrderStatus;
use yii\db\Expression;

class MessageEventHandler extends SSEBase
{
    /**
     * @return bool
     */
    public function check()
    {
        return true;
    }

    /**
     * @return string
     */
    public function update()
    {
        sleep(5);

        $pending_orders = $this->getPendingOrders();
        $push_data = $this->getPushData();
        $audio = $this->getNotificationAudio();

        $balance = Yii::$app->turbosms->balance;
        $balance_srv = Yii::$app->serviceSms->balance;

        $send_data = [
            'push' => [],
            'audio' => [],
            'turbosms' => [],
            'servicesms' => [],
        ];

        if (!empty($push_data)) $send_data['push'] = $push_data;

        if (isset($balance)) $send_data['turbosms'] = $balance;

        if (isset($balance_srv)) $send_data['servicesms'] = $balance_srv;

        if (!empty($pending_orders)) $send_data['audio'] = $audio;

        return json_encode($send_data);
    }

    /**
     * @return array
     */
    private function getPushData()
    {
        $requests = WmOffer::find()
            ->select([
                "status",
            ])
            ->where(['status' => WmOffer::STATUS_WAITING])
            ->count();

        $checkouts = WmCheckout::find()
            ->select([
                "status",
            ])
            ->where(['status' => WmCheckout::IN_PROCESSING])
            ->count();

        $start = StealDataSent::find()
            ->select([
                "status",
            ])
            ->where(['status' => StealDataSent::STATUS_NOT_VIEW])
            ->count();

        $counter = [
            'requests' => $requests,
            'checkouts' => $checkouts,
            'start_up' => $start,
        ];

        if (isset($checkouts) || isset($requests) || isset($start))
        {
            $amount = $requests + $checkouts;
            $counter['sum'] = (string)$amount;
            $counter['start_up'] = $start;
        }

        return $counter;
    }

    /**
     * @return array|OrderView[]
     */
    private function getPendingOrders()
    {
        $pending_orders = OrderView::find()
            ->select([
                "order_id",
                "order_status",
            ])
            ->where([
                'owner_id' => Yii::$app->user->identity->getId(),
            ])
            ->andWhere(['in', 'order_status', [OrderStatus::PENDING, OrderStatus::BACK_TO_PENDING]])
            ->andWhere(['created_at' => new Expression('NOW()')])
            ->asArray()
            ->all();

        return $pending_orders;
    }

    /**
     * @return mixed
     */
    private function getNotificationAudio()
    {
        $audio = BaseProfile::find()
            ->select(['notification_audio'])
            ->where(['user_id' => Yii::$app->user->identity->getId()])
            ->one();

        return $audio->notification_audio;
    }
}