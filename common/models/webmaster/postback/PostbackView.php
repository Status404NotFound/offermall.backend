<?php

namespace common\models\webmaster\postback;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "postback_view".
 *
 * @property integer $order_id
 * @property integer $order_hash
 * @property integer $order_status
 * @property integer $wm_offer_target_status
 * @property double $wm_commission
 * @property double $usd_wm_commission
 * @property string $country_name
 * @property string $offer_name
 * @property integer $t_wm_active
 * @property integer $t_wm_group_active
 * @property string $referrer
 * @property string $sub_id_1
 * @property string $sub_id_2
 * @property string $sub_id_3
 * @property string $sub_id_4
 * @property string $sub_id_5
 * @property string $view_time
 * @property string $view_hash
 * @property string $browser
 * @property string $os
 * @property string $ip
 * @property string $flow_key
 * @property integer $flow_id
 * @property integer $wm_id
 * @property string $wm_name
 */
class PostbackView extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'postback_view';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'order_hash', 'order_status', 'wm_offer_target_status', 't_wm_active', 't_wm_group_active', 'flow_id', 'wm_id'], 'integer'],
            [['wm_commission', 'usd_wm_commission'], 'number'],
            [['country_name', 'offer_name', 'referrer', 'sub_id_1', 'sub_id_2', 'sub_id_3', 'sub_id_4', 'sub_id_5', 'view_time', 'view_hash', 'browser', 'flow_key', 'wm_name'], 'string', 'max' => 255],
            [['os', 'ip'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_id' => Yii::t('app', 'Order ID'),
            'order_hash' => Yii::t('app', 'Order Hash'),
            'order_status' => Yii::t('app', 'Order Status'),
            'wm_offer_target_status' => Yii::t('app', 'Wm Offer Target Status'),
            'wm_commission' => Yii::t('app', 'Wm Commission'),
            'usd_wm_commission' => Yii::t('app', 'Usd Wm Commission'),
            'country_name' => Yii::t('app', 'Country Name'),
            'offer_name' => Yii::t('app', 'Offer Name'),
            't_wm_active' => Yii::t('app', 'Target Wm Active'),
            't_wm_group_active' => Yii::t('app', 'Target Wm Group Active'),
            'referrer' => Yii::t('app', 'Referrer'),
            'sub_id_1' => Yii::t('app', 'Sub Id 1'),
            'sub_id_2' => Yii::t('app', 'Sub Id 2'),
            'sub_id_3' => Yii::t('app', 'Sub Id 3'),
            'sub_id_4' => Yii::t('app', 'Sub Id 4'),
            'sub_id_5' => Yii::t('app', 'Sub Id 5'),
            'view_time' => Yii::t('app', 'View Time'),
            'view_hash' => Yii::t('app', 'View Hash'),
            'browser' => Yii::t('app', 'Browser'),
            'os' => Yii::t('app', 'Os'),
            'ip' => Yii::t('app', 'Ip'),
            'flow_key' => Yii::t('app', 'Flow Key'),
            'flow_id' => Yii::t('app', 'Flow ID'),
            'wm_id' => Yii::t('app', 'Wm ID'),
            'wm_name' => Yii::t('app', 'Wm Name'),
        ];
    }
}
