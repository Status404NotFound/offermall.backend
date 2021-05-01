<?php

namespace common\models\log;

use common\models\BaseModel;
use common\modules\user\models\tables\User;
use Yii;

/**
 * This is the model class for table "logs".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $ip
 * @property string $model
 * @property string $last_data
 * @property string $new_data
 * @property string $datetime
 * @property string $comment
 *
 * @property User $user
 */
class Logs extends BaseModel
{
    public $created_at = false;
    public $created_by = false;
    public $updated_at = false;
    public $updated_by = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'logs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ip', 'model', 'new_data', 'comment'], 'required'],
            [['user_id'], 'integer'],
            [['last_data', 'new_data', 'comment'], 'string'],
            [['datetime'], 'safe'],
            [['ip', 'model'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'ip' => Yii::t('app', 'Ip'),
            'model' => Yii::t('app', 'Model'),
            'last_data' => Yii::t('app', 'Last Data'),
            'new_data' => Yii::t('app', 'New Data'),
            'datetime' => Yii::t('app', 'Datetime'),
            'comment' => Yii::t('app', 'Comment'),
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