<?php

namespace common\models\webmaster\postback;

use Yii;
use common\models\BaseModel;
use common\modules\user\models\tables\User;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%postback_global}}".
 *
 * @property integer $postback_id
 * @property integer $wm_id
 * @property string $url
 * @property string $url_approved
 * @property string $url_cancelled
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $wm
 */
class PostbackGlobal extends BaseModel
{
    public $created_by = false;
    public $updated_by = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%postback_global}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['wm_id'], 'required'],
            [['wm_id'], 'integer'],
            [['url', 'url_approved', 'url_cancelled', 'url_notValid'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['wm_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['wm_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'postback_id' => Yii::t('app', 'Postback ID'),
            'wm_id' => Yii::t('app', 'Wm ID'),
            'url' => Yii::t('app', 'Url'),
            'url_approved' => Yii::t('app', 'Url Approved'),
            'url_cancelled' => Yii::t('app', 'Url Cancelled'),
            'url_notValid' => Yii::t('app', 'Url Not Valid'),
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

    /**
     * @param null $wm_id
     * @return array|null|PostbackGlobal|\yii\db\ActiveRecord
     */
    public function getGlobalPostback($wm_id = null)
    {
        if (isset($wm_id))
        {
            return PostbackGlobal::find()
                ->select([
                    'url',
                    'url_approved',
                    'url_cancelled',
                    'url_notValid',
                ])
                ->where(['wm_id' => $wm_id])
                ->asArray()
                ->one();
        }

        return null;
    }
}
