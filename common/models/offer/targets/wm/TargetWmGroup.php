<?php

namespace common\models\offer\targets\wm;

use Yii;
use common\modules\user\models\tables\User;
use common\models\BaseModel;

/**
 * This is the model class for table "target_wm_group".
 *
 * @property integer $target_wm_group_id
 * @property integer $wm_offer_target_id
 *
 * @property double $base_commission
 * @property double $exceeded_commission
 * @property integer $use_commission_rules
 *
 * @property integer $hold
 *
 * @property integer $active
 * @property integer $view_for_all
 *
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @property TargetWm[] $targetWms
 * @property User $updatedBy
 * @property WmOfferTarget $wmOfferTarget
 * @property TargetWmGroupRules[] $targetWmGroupRules
 */
class TargetWmGroup extends BaseModel
{
    public $created_at = false;
    public $created_by = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'target_wm_group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['wm_offer_target_id'], 'required'],
            [['wm_offer_target_id', 'use_commission_rules', 'hold', 'active', 'view_for_all', 'updated_by'], 'integer'],
            [['updated_at', 'base_commission', 'exceeded_commission', 'target_wm_group_id'], 'safe'],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
            [['wm_offer_target_id'], 'exist', 'skipOnError' => true, 'targetClass' => WmOfferTarget::className(), 'targetAttribute' => ['wm_offer_target_id' => 'wm_offer_target_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'target_wm_group_id' => Yii::t('app', 'Target Wm Group ID'),
            'wm_offer_target_id' => Yii::t('app', 'Wm Offer Target ID'),
            'base_commission' => Yii::t('app', 'Base Commission'),
            'exceeded_commission' => Yii::t('app', 'Exceeded Commission'),
            'use_commission_rules' => Yii::t('app', 'Use Commission Rules'),
            'hold' => Yii::t('app', 'Hold'),
            'active' => Yii::t('app', 'Active'),
            'view_for_all' => Yii::t('app', 'View For All'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetWms()
    {
        return $this->hasMany(TargetWm::className(), ['target_wm_group_id' => 'target_wm_group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWmOfferTarget()
    {
        return $this->hasOne(WmOfferTarget::className(), ['wm_offer_target_id' => 'wm_offer_target_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetWmGroupRules()
    {
        return $this->hasMany(TargetWmGroupRules::className(), ['target_wm_group_id' => 'target_wm_group_id']);
    }
}