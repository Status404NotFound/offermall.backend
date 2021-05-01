<?php

namespace common\models\offer\targets\wm;

use common\modules\user\models\tables\User;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "target_wm_group_rules_view".
 *
 * @property integer $rule_id
 * @property integer $target_wm_group_id
 * @property integer $amount
 * @property double $commission
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @property TargetWmGroup $targetWmGroup
 * @property User $updatedBy
 */
class TargetWmGroupRulesView extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'target_wm_group_rules_view';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['target_wm_group_id', 'amount', 'commission', 'updated_by'], 'required'],
            [['target_wm_group_id', 'amount', 'updated_by'], 'integer'],
            [['commission'], 'number'],
            [['updated_at'], 'safe'],
            [['target_wm_group_id', 'amount'], 'unique', 'targetAttribute' => ['target_wm_group_id', 'amount'], 'message' => 'The combination of Target Wm Group ID and Amount has already been taken.'],
            [['target_wm_group_id'], 'exist', 'skipOnError' => true, 'targetClass' => TargetWmGroup::className(), 'targetAttribute' => ['target_wm_group_id' => 'target_wm_group_id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rule_id' => Yii::t('app', 'Rule ID'),
            'target_wm_group_id' => Yii::t('app', 'Target Wm Group ID'),
            'amount' => Yii::t('app', 'Amount'),
            'commission' => Yii::t('app', 'Commission'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetWmGroup()
    {
        return $this->hasOne(TargetWmGroup::className(), ['target_wm_group_id' => 'target_wm_group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }
}