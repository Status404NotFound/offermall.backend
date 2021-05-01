<?php

namespace common\modules\user\models\tables;

use Yii;

/**
 * This is the model class for table "{{%user_session}}".
 *
 * @property integer $session_id
 * @property integer $user_id
 * @property string $expire
 * @property string $data
 * @property string $last_write
 */
class UserSession extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_session}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['data'], 'string'],
            [['expire'], 'string'],
            [['last_write'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'session_id' => Yii::t('app', 'Session ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'data' => Yii::t('app', 'Data'),
            'expire' => Yii::t('app', 'Expire'),
            'last_write' => Yii::t('app', 'Last Write'),
        ];
    }
}
