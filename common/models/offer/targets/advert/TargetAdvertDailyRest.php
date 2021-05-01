<?php

namespace common\models\offer\targets\advert;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "target_advert_daily_rest".
 *
 * @property integer $target_advert_id
 * @property integer $rest
 * @property string $date
 *
 * @property TargetAdvert $targetAdvert
 */
class TargetAdvertDailyRest extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'target_advert_daily_rest';
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