<?php

namespace common\models\callcenter;

use Yii;

/**
 * This is the model class for table "asterisk_queue_member".
 *
 * @property integer $uniqueid
 * @property string $membername
 * @property string $queue_name
 * @property string $interface
 * @property integer $penalty
 * @property integer $paused
 *
 * @property AsteriskQueue $queueName
 */
class AsteriskQueueMember extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'asterisk_queue_member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['penalty', 'paused'], 'integer'],
            [['membername'], 'string', 'max' => 40],
            [['queue_name', 'interface'], 'string', 'max' => 128],
            [['queue_name', 'interface'], 'unique', 'targetAttribute' => ['queue_name', 'interface'], 'message' => 'The combination of Queue Name and Interface has already been taken.'],
            [['queue_name'], 'exist', 'skipOnError' => true, 'targetClass' => AsteriskQueue::className(), 'targetAttribute' => ['queue_name' => 'name']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uniqueid' => Yii::t('app', 'Uniqueid'),
            'membername' => Yii::t('app', 'Membername'),
            'queue_name' => Yii::t('app', 'Queue Name'),
            'interface' => Yii::t('app', 'Interface'),
            'penalty' => Yii::t('app', 'Penalty'),
            'paused' => Yii::t('app', 'Paused'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQueueName()
    {
        return $this->hasOne(AsteriskQueue::className(), ['name' => 'queue_name']);
    }
}
