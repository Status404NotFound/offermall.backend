<?php

namespace common\models\callcenter;

use Yii;

/**
 * This is the model class for table "call_queue".
 *
 * @property integer $queue_id
 * @property string $offer
 * @property string $geo
 * @property string $language
 * @property string $attempts
 * @property string $lead_status
 * @property string $queue_asterisk_code
 * @property string $queue_name
 * @property string $created_at
 * @property string $updated_at
 */
class CallQueue extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'call_queue';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['offer', 'geo', 'language', 'attempts', 'lead_status', 'queue_name', 'queue_asterisk_code'], 'string'],
            [['created_at', 'updated_at', 'advert_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'queue_id' => Yii::t('app', 'Queue ID'),
            'offer' => Yii::t('app', 'Offer'),
            'geo' => Yii::t('app', 'Geo'),
            'language' => Yii::t('app', 'Language'),
            'attempts' => Yii::t('app', 'Attempts'),
            'lead_status' => Yii::t('app', 'Lead Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
