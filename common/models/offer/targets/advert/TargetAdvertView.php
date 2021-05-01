<?php

namespace common\models\offer\targets\advert;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "target_advert_view".
 *
 * @property integer $offer_id
 * @property integer $advert_offer_target_id
 * @property integer $target_advert_group_id
 * @property integer $target_advert_id
 * @property integer $advert_offer_target_status
 * @property integer $geo_id
 * @property string $geo_name
 * @property integer $advert_id
 * @property string $advert_name
 * @property integer $stock_id
 * @property integer $daily_limit
 * @property integer $currency_id
 * @property string $currency_name
 * @property double $base_commission
 * @property double $exceeded_commission
 * @property integer $use_commission_rules
 * @property integer $wm_visible
 * @property integer $send_sms_customer
 * @property integer $send_second_sms_customer
 * @property integer $send_sms_owner
 * @property string $sms_text_customer
 * @property string $second_sms_text_customer
 * @property string $sms_text_owner
 * @property integer $aot_active
 * @property integer $tag_active
 * @property integer $ta_active
 */
class TargetAdvertView extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'target_advert_view';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['offer_id', 'target_advert_group_id', 'advert_offer_target_status', 'geo_id', 'geo_name', 'advert_id', 'advert_name', 'currency_id', 'currency_name'], 'required'],
            [['offer_id', 'advert_offer_target_id', 'target_advert_group_id', 'target_advert_id', 'advert_offer_target_status', 'geo_id', 'advert_id', 'stock_id', 'daily_limit', 'currency_id', 'use_commission_rules', 'wm_visible', 'send_sms_customer', 'send_sms_owner', 'send_second_sms_customer', 'aot_active', 'tag_active', 'ta_active'], 'integer'],
            [['base_commission', 'exceeded_commission'], 'number'],
            [['geo_name', 'advert_name', 'currency_name', 'sms_text_customer', 'sms_text_owner', 'second_sms_text_customer'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'offer_id' => Yii::t('app', 'Offer ID'),
            'advert_offer_target_id' => Yii::t('app', 'Advert Offer Target ID'),
            'target_advert_group_id' => Yii::t('app', 'Target Advert Group ID'),
            'target_advert_id' => Yii::t('app', 'Target Advert ID'),
            'advert_offer_target_status' => Yii::t('app', 'Advert Offer Target Status'),
            'geo_id' => Yii::t('app', 'Geo ID'),
            'geo_name' => Yii::t('app', 'Geo Name'),
            'advert_id' => Yii::t('app', 'Advert ID'),
            'advert_name' => Yii::t('app', 'Advert Name'),
            'stock_id' => Yii::t('app', 'Stock ID'),
            'daily_limit' => Yii::t('app', 'Daily Limit'),
            'currency_id' => Yii::t('app', 'Currency ID'),
            'currency_name' => Yii::t('app', 'Currency Name'),
            'base_commission' => Yii::t('app', 'Base Commission'),
            'exceeded_commission' => Yii::t('app', 'Exceeded Commission'),
            'use_commission_rules' => Yii::t('app', 'Use Commission Rules'),
            'wm_visible' => Yii::t('app', 'Wm Visible'),
            'send_sms_customer' => Yii::t('app', 'Send Sms Customer'),
            'send_sms_owner' => Yii::t('app', 'Send Sms Owner'),
            'send_second_sms_customer' => Yii::t('app', 'Send Second Sms Customer'),
            'sms_text_customer' => Yii::t('app', 'Sms Text Customer'),
            'sms_second_text_customer' => Yii::t('app', 'Sms Second Text Customer'),
            'sms_text_owner' => Yii::t('app', 'Sms Text Owner'),
            'aot_active' => Yii::t('app', 'Aot Active'),
            'tag_active' => Yii::t('app', 'Tag Active'),
            'ta_active' => Yii::t('app', 'Ta Active'),
        ];
    }

    /**
     * @param $select
     * @param $where
     * @param $groupBy
     * @return array|ActiveRecord[]
     */
    public static function getStatusGeos($select, $where, $groupBy)
    {
        return self::find()
            ->select($select)
            ->where($where)
            ->groupBy($groupBy)
            ->asArray()
            ->all();
    }
}