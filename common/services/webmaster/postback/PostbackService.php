<?php

namespace common\services\webmaster\postback;

use common\models\offer\targets\wm\WmOfferTarget;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\models\order\StatusReason;
use common\models\webmaster\postback\PostbackView;
use common\models\webmaster\postback\PostbackGlobal;
use common\models\webmaster\postback\PostbackIndividual;
use linslin\yii2\curl\Curl;

/**
 * Class PostbackService
 * @package common\services\webmaster\postback
 */
class PostbackService
{
    public $order_id;
    public $order_data;
    public $mode;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * PostbackService constructor.
     * @param $order_id
     * @param $mode
     */
    public function __construct($order_id, $mode)
    {
        $this->order_id = $order_id;
        $this->order_data = $this->getPostbackData();
        $this->mode = $mode;
        $this->doPostback();
    }

    /**
     * @return array
     */
    private function getPostbackData()
    {
        $query = PostbackView::find()
            ->select([
                'order_id',
                'order_hash as transfer_id',
                'order_status as status',
                'wm_offer_target_status as target',
                "IF(wm_commission is null or wm_commission = '', '0', wm_commission) as commission",
                'country_name as country',
                'offer_name as offer',
                'referrer',
                'sub_id_1 as subid1',
                'sub_id_2 as subid2',
                'sub_id_3 as subid3',
                'sub_id_4 as subid4',
                'sub_id_5 as subid5',
                'flow_key as flow',
                'flow_id',
                'browser',
                'wm_id',
            ])
            ->where(['order_id' => $this->order_id])
            ->andWhere(['t_wm_active' => 1])
            ->asArray()
            ->one();

        if ($query) {
            $query['geo'] = Order::find()->where(['order_id' => $query['order_id']])->one()->customer->country->country_name;
            $query['status'] = OrderStatus::attributeLabels($query['status']);
            $query['target'] = isset($query['target']) ? OrderStatus::attributeLabels($query['target']) : 0;
        }

        if (OrderStatus::statusNeedReason($query['status']) === true) {
            $query['reason'] = StatusReason::getReason($query['status'], $query['status']);
        }

        return $query;
    }

    /**
     * @return array|PostbackGlobal|PostbackIndividual|null|\yii\db\ActiveRecord
     */
    private function checkPostback()
    {
        $global = (new PostbackGlobal())->getGlobalPostback($this->order_data['wm_id']);
        $individual = (new PostbackIndividual())->getIndividualPostback($this->order_data['flow_id']);

        if (isset($individual)) {
            $urls = $individual;
        } elseif (isset($global)) {
            $urls = $global;
        } else {
            return null;
        }

        return $urls ?? null;
    }

    /**
     * @param $type
     * @return bool
     */
    private function postbackType($type)
    {
        if (empty($type)) {
            $this->errors['type'] = 'Postback type must be set correctly!';
            return false;
        }

        $type_names = [
            'url_approved', 'url_cancelled', 'url'
        ];

        return in_array(strtolower($type), $type_names, true);
    }

    /**
     * Send Postback data
     */
    private function doPostback()
    {
        $curl = new Curl();
        $urls = $this->checkPostback();
        if (!is_null($urls) && $this->postbackType($this->mode)) {
            foreach ($this->order_data as $name => $value) {
                $value = str_replace(' ', '_', $value);
                $urls[$this->mode] = str_replace('{' . $name . '}', $value, $urls[$this->mode]);
            }
            $curl->get($urls[$this->mode]);
        } else {
            return null;
        }
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}