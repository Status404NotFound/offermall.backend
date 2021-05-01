<?php

namespace common\models\customer;

use Yii;
use common\models\BaseModel;

/**
 * This is the model class for table "{{%customer_blacklist}}".
 *
 * @property int $customer_black_list_id
 * @property string $ip
 * @property string $phone
 * @property string $email
 * @property int $reason_id
 * @property int $status_id
 * @property int $is_active
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property BlackListAttempt[] $blacklistAttempts
 */
class CustomerBlackList extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_blacklist}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status_id', 'reason_id'], 'required'],
            [['ip', 'phone', 'email'], 'unique'],
            [['reason_id', 'status_id', 'is_active', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['ip', 'phone', 'email'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'customer_black_list_id' => Yii::t('app', 'Customer Black List ID'),
            'ip' => Yii::t('app', 'Ip'),
            'phone' => Yii::t('app', 'Phone'),
            'email' => Yii::t('app', 'Email'),
            'reason_id' => Yii::t('app', 'Reason ID'),
            'status_id' => Yii::t('app', 'Status ID'),
            'is_active' => Yii::t('app', 'Is Active'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBlacklistAttempts()
    {
        return $this->hasMany(BlackListAttempt::className(), ['customer_black_list_id' => 'customer_black_list_id']);
    }

    /**
     * @return array|CustomerBlacklist[]|\yii\db\ActiveRecord[]
     */
    public static function getCustomersInBlacklist()
    {
        return self::find()
            ->select(['customer_black_list_id as id', 'ip', 'phone', 'email', 'status_id', 'reason_id'])
            ->where(['is_active' => 1])
            ->asArray()
            ->all();
    }

    /**
     * @return bool
     */
    public function getIsBlocked()
    {
        return $this->is_active != 1;
    }

    /**
     * @param $status_id
     * @param $reason_id
     * @return bool
     */
    public function block($status_id, $reason_id)
    {
        return (bool)$this->updateAttributes([
            'is_active' => 0,
            'status_id' => $status_id,
            'reason_id' => $reason_id,
        ]);
    }

    /**
     * @param $status_id
     * @param $reason_id
     * @return bool
     */
    public function unblock($status_id, $reason_id)
    {
        return (bool)$this->updateAttributes([
            'is_active' => 1,
            'status_id' => $status_id,
            'reason_id' => $reason_id,
        ]);
    }
}
