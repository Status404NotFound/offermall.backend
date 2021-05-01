<?php

namespace common\models\callcenter;
use Yii;

/**
 * This is the model class for table "operator_queue".
 *
 * @property integer $operator_queue_id
 * @property integer $operator_id
 * @property integer $call_queue_id
 *
 * @property CallQueue $callQueue
 */
class OperatorQueue extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'operator_queue';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['operator_id', 'call_queue_id'], 'required'],
            [['operator_id', 'call_queue_id'], 'integer'],
            [['call_queue_id'], 'exist', 'skipOnError' => true, 'targetClass' => CallQueue::className(), 'targetAttribute' => ['call_queue_id' => 'queue_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'operator_queue_id' => Yii::t('app', 'Operator Queue ID'),
            'operator_id' => Yii::t('app', 'Operator ID'),
            'call_queue_id' => Yii::t('app', 'Call Queue ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCallQueue()
    {
        return $this->hasOne(CallQueue::className(), ['queue_id' => 'call_queue_id']);
    }
}
