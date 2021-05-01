<?php

namespace common\models\offer\targets\wm;

use common\modules\user\models\tables\User;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "target_wm".
 *
 * @property integer $target_wm_id
 * @property integer $target_wm_group_id
 * @property integer $wm_id
 * @property integer $excepted
 * @property integer $active
 *
 * @property TargetWmGroup $targetWmGroup
 * @property User $wm
 */
class TargetWm extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'target_wm';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['target_wm_group_id'], 'required'],
            [['target_wm_group_id', 'excepted', 'active'], 'integer'],
            [['target_wm_group_id'], 'exist', 'skipOnError' => true, 'targetClass' => TargetWmGroup::className(), 'targetAttribute' => ['target_wm_group_id' => 'target_wm_group_id']],
            [['wm_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['wm_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'target_wm_id' => Yii::t('app', 'Target Wm ID'),
            'target_wm_group_id' => Yii::t('app', 'Target Wm Group ID'),
            'wm_id' => Yii::t('app', 'Wm ID'),
            'excepted' => Yii::t('app', 'Excepted'),
            'active' => Yii::t('app', 'Active'),
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
    public function getWm()
    {
        return $this->hasOne(User::className(), ['id' => 'wm_id']);
    }
}