<?php

namespace common\models\offer\targets\advert;

use common\models\finance\Currency;
use common\modules\user\models\tables\User;
use common\models\BaseModel;
use Yii;

/**
 * This is the model class for table "target_advert_group".
 *
 * @property integer $target_advert_group_id
 * @property integer $advert_offer_target_id
 * @property integer $daily_limit
 * @property integer $currency_id
 * @property double $base_commission
 * @property double $exceeded_commission
 * @property integer $use_commission_rules
 * @property integer $send_sms_customer
 * @property integer $send_second_sms_customer
 * @property integer $send_sms_owner
 * @property string $sms_text_customer
 * @property string $second_sms_text_customer
 * @property string $sms_text_owner
 * @property integer $active
 * @property integer $pay_online
 * @property string $updated_at
 * @property integer $updated_by
 * @property integer $send_to_lp_crm
 * @property integer $auto_send_to_lp_crm
 * @property integer $send_to_my_land_crm
 * @property integer $auto_send_to_my_land_crm
 *
 * @property TargetAdvert[] $targetAdverts
 * @property User[] $adverts
 * @property TargetAdvertGroupRules[] $targetAdvertGroupRules
 * @property AdvertOfferTarget $advertOfferTarget
 * @property Currency $currency
 * @property User $updatedBy
 */
class TargetAdvertGroup extends BaseModel
{
    public $created_at = false;
    public $created_by = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'target_advert_group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['advert_offer_target_id', 'currency_id'], 'required'],
            [['send_to_lp_crm', 'auto_send_to_lp_crm', 'send_to_my_land_crm', 'auto_send_to_my_land_crm', 'advert_offer_target_id', 'daily_limit', 'currency_id', 'use_commission_rules', 'send_sms_customer', 'send_second_sms_customer', 'send_sms_owner', 'active', 'pay_online', 'updated_by'], 'integer'],
            [['base_commission', 'exceeded_commission'], 'number'],
            [['updated_at'], 'safe'],
            [['sms_text_customer', 'second_sms_text_customer', 'sms_text_owner'], 'string', 'max' => 255],
            [['advert_offer_target_id'], 'exist', 'skipOnError' => true, 'targetClass' => AdvertOfferTarget::className(), 'targetAttribute' => ['advert_offer_target_id' => 'advert_offer_target_id']],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['currency_id' => 'currency_id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'target_advert_group_id' => Yii::t('app', 'Target Advert Group ID'),
            'advert_offer_target_id' => Yii::t('app', 'Advert Offer Target ID'),
            'daily_limit' => Yii::t('app', 'Daily Limit'),
            'currency_id' => Yii::t('app', 'Currency ID'),
            'base_commission' => Yii::t('app', 'Base Commission'),
            'exceeded_commission' => Yii::t('app', 'Exceeded Commission'),
            'use_commission_rules' => Yii::t('app', 'Use Commission Rules'),
            'send_sms_customer' => Yii::t('app', 'Send Sms Customer'),
            'send_second_sms_customer' => Yii::t('app', 'Send Second Sms Customer'),
            'send_sms_owner' => Yii::t('app', 'Send Sms Owner'),
            'sms_text_customer' => Yii::t('app', 'Sms Text Customer'),
            'second_sms_text_customer' => Yii::t('app', 'Second Sms Text Customer'),
            'sms_text_owner' => Yii::t('app', 'Sms Text Owner'),
            'active' => Yii::t('app', 'Active'),
            'pay_online' => Yii::t('app', 'Pay Online'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'send_to_lp_crm' => Yii::t('app', 'Send To LP CRM'),
            'auto_send_to_lp_crm' => Yii::t('app', 'Auto Send To LP CRM'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetAdverts()
    {
        return $this->hasMany(TargetAdvert::className(), ['target_advert_group_id' => 'target_advert_group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdverts()
    {
        return $this->hasMany(User::className(), ['id' => 'advert_id'])->viaTable('target_advert', ['target_advert_group_id' => 'target_advert_group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvertOfferTarget()
    {
        return $this->hasOne(AdvertOfferTarget::className(), ['advert_offer_target_id' => 'advert_offer_target_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetAdvertGroupRules()
    {
        return $this->hasMany(TargetAdvertGroupRules::className(), ['target_advert_group_id' => 'target_advert_group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['currency_id' => 'currency_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    public static function getAdvertGroupByTargetId($advert_offer_target_id)
    {
        return self::find()
            ->select(['target_advert_group_id', 'base_commission', 'exceeded_commission', 'use_commission_rules',
                'currency_id', 'daily_limit', 'active', 'pay_online', 'send_to_lp_crm', 'auto_send_to_lp_crm', 'send_to_my_land_crm', 'auto_send_to_my_land_crm'])
            ->where(['advert_offer_target_id' => $advert_offer_target_id])
            ->asArray()
            ->all();
    }

    public static function getAdvertGroupNotifyByTargetId($advert_offer_target_id)
    {
        $query = self::find()
            ->select(['target_advert_group_id', 'send_sms_customer', 'send_sms_owner',
                'sms_text_customer', 'send_sms_owner', 'sms_text_owner', 'send_second_sms_customer', 'second_sms_text_customer'])
            ->where(['advert_offer_target_id' => $advert_offer_target_id])
            ->asArray()
            ->all();

        foreach ($query as &$value) {
            $value['send_sms_customer'] = (boolean)$value['send_sms_customer'];
            $value['send_sms_owner'] = (boolean)$value['send_sms_owner'];
            $value['send_second_sms_customer'] = (boolean)$value['send_second_sms_customer'];
        }

        return $query;
    }
}
