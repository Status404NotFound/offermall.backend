<?php

namespace common\models\offer\targets\advert;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "target_advert_daily_rest_log".
 *
 * @property integer $target_advert_id
 * @property integer $rest
 * @property string $date
 * @property string $message
 *
 * @property TargetAdvert $targetAdvert
 */
class TargetAdvertDailyRestLog extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'target_advert_daily_rest_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['target_advert_id'], 'required'],
            [['target_advert_id', 'rest'], 'integer'],
            [['date'], 'safe'],
            [['message'], 'string', 'max' => 1023],
            [['target_advert_id'], 'exist', 'skipOnError' => true, 'targetClass' => TargetAdvert::className(), 'targetAttribute' => ['target_advert_id' => 'target_advert_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'target_advert_id' => Yii::t('app', 'Target Advert ID'),
            'rest' => Yii::t('app', 'Rest'),
            'date' => Yii::t('app', 'Date'),
            'message' => Yii::t('app', 'Message'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetAdvert()
    {
        return $this->hasOne(TargetAdvert::className(), ['target_advert_id' => 'target_advert_id']);
    }
}