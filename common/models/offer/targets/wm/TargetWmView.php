<?php

namespace common\models\offer\targets\wm;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "target_wm_view".
 *
 * @property integer $offer_id
 * @property integer $target_wm_group_id
 * @property integer $wm_offer_target_id
 * @property integer $advert_offer_target_status
 * @property integer $wm_offer_target_status
 * @property integer $geo_id
 * @property string $geo_name
 * @property integer $wm_id
 * @property string $wm_name
 * @property double $base_commission
 * @property double $exceeded_commission
 * @property integer $use_commission_rules
 * @property integer $hold
 * @property integer $wot_active
 * @property integer $twm_active
 * @property integer $view_for_all
 * @property integer $excepted
 */
class TargetWmView extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'target_wm_view';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['offer_id', 'target_wm_group_id', 'wm_offer_target_id', 'advert_offer_target_status', 'wm_offer_target_status', 'geo_id', 'wm_id', 'use_commission_rules', 'hold', 'wot_active', 'twm_active', 'view_for_all', 'excepted'], 'integer'],
            [['geo_name'], 'required'],
            [['base_commission', 'exceeded_commission'], 'number'],
            [['geo_name', 'wm_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'offer_id' => Yii::t('app', 'Offer ID'),
            'target_wm_group_id' => Yii::t('app', 'Target Wm Group ID'),
            'wm_offer_target_id' => Yii::t('app', 'Wm Offer Target ID'),
            'advert_offer_target_status' => Yii::t('app', 'Advert Offer Target Status'),
            'wm_offer_target_status' => Yii::t('app', 'Wm Offer Target Status'),
            'geo_id' => Yii::t('app', 'Geo ID'),
            'geo_name' => Yii::t('app', 'Geo Name'),
            'wm_id' => Yii::t('app', 'Wm ID'),
            'wm_name' => Yii::t('app', 'Wm Name'),
            'base_commission' => Yii::t('app', 'Base Commission'),
            'exceeded_commission' => Yii::t('app', 'Exceeded Commission'),
            'use_commission_rules' => Yii::t('app', 'Use Commission Rules'),
            'hold' => Yii::t('app', 'Hold'),
            'wot_active' => Yii::t('app', 'Wot Active'),
            'twm_active' => Yii::t('app', 'Twm Active'),
            'view_for_all' => Yii::t('app', 'View For All'),
            'excepted' => Yii::t('app', 'Excepted'),
        ];
    }
}