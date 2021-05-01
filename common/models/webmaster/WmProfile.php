<?php

namespace common\models\webmaster;

use Yii;
use common\modules\user\models\tables\User;
use common\models\BaseModel;

/**
 * This is the model class for table "{{%wm_profile}}".
 *
 * @property integer $wm_profile_id
 * @property integer $wm_id
 * @property string $skype
 * @property string $telegram
 * @property string $facebook
 * @property string $card
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $wm
 */
class WmProfile extends BaseModel
{
    public $timezone;

    public $created_by = false;
    public $updated_by = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wm_profile}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['wm_id', 'card'], 'required'],
            [['wm_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['skype', 'telegram', 'facebook', 'card'], 'string', 'max' => 255],
            [['wm_id'], 'unique'],
            [['wm_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['wm_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'wm_profile_id' => Yii::t('app', 'Wm Profile ID'),
            'wm_id' => Yii::t('app', 'Wm ID'),
            'skype' => Yii::t('app', 'Skype'),
            'telegram' => Yii::t('app', 'Telegram'),
            'facebook' => Yii::t('app', 'Facebook'),
            'card' => Yii::t('app', 'Card'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWm()
    {
        return $this->hasOne(User::className(), ['id' => 'wm_id']);
    }
}
