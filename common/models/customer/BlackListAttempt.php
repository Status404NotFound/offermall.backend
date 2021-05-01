<?php

namespace common\models\customer;

use Yii;

/**
 * This is the model class for table "{{%black_list_attempt}}".
 *
 * @property int $black_list_attempt_id
 * @property string $date
 * @property int $customer_black_list_id
 * @property int $status_id
 * @property int $reason_id
 * @property int $attempts
 * @property string $updated_at
 */
class BlackListAttempt extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%black_list_attempt}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'customer_black_list_id', 'status_id', 'reason_id'], 'required'],
            [['date', 'updated_at'], 'safe'],
            [['customer_black_list_id', 'status_id', 'reason_id', 'attempts'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'black_list_attempt_id' => Yii::t('app', 'Black List Attempt ID'),
            'date' => Yii::t('app', 'Date'),
            'customer_black_list_id' => Yii::t('app', 'Customer Black List ID'),
            'status_id' => Yii::t('app', 'Status ID'),
            'reason_id' => Yii::t('app', 'Reason ID'),
            'attempts' => Yii::t('app', 'Attempts'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
