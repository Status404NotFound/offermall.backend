<?php

namespace common\models\log\login;

use common\modules\user\models\tables\User;
use yii\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "login_log".
 *
 * @property integer $login_log_id
 * @property integer $user_id
 * @property string $datetime
 *
 * @property User $user
 */
class LoginLog extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'login_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['datetime'], 'safe'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'login_log_id' => Yii::t('app', 'Login Log ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'datetime' => Yii::t('app', 'Datetime'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}