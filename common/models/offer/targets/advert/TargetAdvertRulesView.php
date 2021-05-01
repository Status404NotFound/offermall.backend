<?php

namespace common\models\offer\targets\advert;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "target_advert_group_rules_view".
 *
 * @property integer $offer_id
 * @property integer $target_advert_group_id
 * @property integer $target_advert_id
 * @property integer $advert_id
 * @property string $advert_name
 * @property integer $daily_limit
 * @property integer $use_commission_rules
 * @property double $base_commission
 * @property double $exceeded_commission
 * @property integer $rule_id
 * @property integer $amount
 * @property double $commission
 * @property integer $stock_id
 * @property integer $currency_id
 * @property string $currency_name
 * @property integer $send_sms
 * @property string $sms_text
 * @property integer $tag_active
 * @property integer $ta_active
 */
class TargetAdvertRulesView extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'target_advert_group_rules_view';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['offer_id', 'advert_id', 'advert_name', 'amount', 'commission', 'currency_id', 'currency_name'], 'required'],
            [['offer_id', 'target_advert_group_id', 'target_advert_id', 'advert_id', 'daily_limit', 'use_commission_rules', 'rule_id', 'amount', 'stock_id', 'currency_id', 'send_sms', 'tag_active', 'ta_active'], 'integer'],
            [['base_commission', 'exceeded_commission', 'commission'], 'number'],
            [['advert_name', 'currency_name', 'sms_text'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'offer_id' => Yii::t('app', 'Offer ID'),
            'target_advert_group_id' => Yii::t('app', 'Target Advert Group ID'),
            'target_advert_id' => Yii::t('app', 'Target Advert ID'),
            'advert_id' => Yii::t('app', 'Advert ID'),
            'advert_name' => Yii::t('app', 'Advert Name'),
            'daily_limit' => Yii::t('app', 'Daily Limit'),
            'use_commission_rules' => Yii::t('app', 'Use Commission Rules'),
            'base_commission' => Yii::t('app', 'Base Commission'),
            'exceeded_commission' => Yii::t('app', 'Exceeded Commission'),
            'rule_id' => Yii::t('app', 'Rule ID'),
            'amount' => Yii::t('app', 'Amount'),
            'commission' => Yii::t('app', 'Commission'),
            'stock_id' => Yii::t('app', 'Stock ID'),
            'currency_id' => Yii::t('app', 'Currency ID'),
            'currency_name' => Yii::t('app', 'Currency Name'),
            'send_sms' => Yii::t('app', 'Send Sms'),
            'sms_text' => Yii::t('app', 'Sms Text'),
            'tag_active' => Yii::t('app', 'Tag Active'),
            'ta_active' => Yii::t('app', 'Ta Active'),
        ];
    }
}