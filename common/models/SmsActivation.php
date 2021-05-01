<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use common\modules\user\models\tables\User;

/**
 * This is the model class for table "{{%sms_activation}}".
 *
 * @property integer $sms_id
 * @property integer $user_id
 * @property string $hash
 * @property string $created_at
 *
 * @property User $user
 */
class SmsActivation extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sms_activation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'hash'], 'required'],
            [['user_id'], 'integer'],
            [['created_at'], 'safe'],
            [['hash'], 'string', 'max' => 32],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'sms_id' => Yii::t('app', 'Sms ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'hash' => Yii::t('app', 'Hash'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /** @inheritdoc */
    public function beforeSave($insert) {
        if ($insert) {
            static::deleteAll(['user_id' => $this->user_id]);
        }

        return parent::beforeSave($insert);
    }

    /** @inheritdoc */
    public static function primaryKey() {
        return ['user_id', 'code'];
    }
}
