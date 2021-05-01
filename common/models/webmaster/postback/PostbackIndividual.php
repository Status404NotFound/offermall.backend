<?php

namespace common\models\webmaster\postback;

use Yii;
use common\models\BaseModel;
use common\modules\user\models\tables\User;
use common\models\flow\Flow;

/**
 * This is the model class for table "{{%postback_individual}}".
 *
 * @property integer $postback_individual_id
 * @property integer $wm_id
 * @property integer $flow_id
 * @property string $url
 * @property string $url_approved
 * @property string $url_cancelled
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Flow $flow
 * @property User $wm
 */
class PostbackIndividual extends BaseModel
{
    public $created_by = false;
    public $updated_by = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%postback_individual}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['wm_id', 'flow_id'], 'required'],
            [['wm_id', 'flow_id'], 'integer'],
            [['url', 'url_approved', 'url_cancelled', 'url_notValid'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['flow_id'], 'exist', 'skipOnError' => true, 'targetClass' => Flow::className(), 'targetAttribute' => ['flow_id' => 'flow_id']],
            [['wm_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['wm_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'postback_individual_id' => Yii::t('app', 'Postback Individual ID'),
            'wm_id' => Yii::t('app', 'Wm ID'),
            'flow_id' => Yii::t('app', 'Flow ID'),
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
    public function getFlow()
    {
        return $this->hasOne(Flow::className(), ['flow_id' => 'flow_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWm()
    {
        return $this->hasOne(User::className(), ['id' => 'wm_id']);
    }

    /**
     * @param null $flow_id
     * @param null $wm_id
     * @return array|null|PostbackIndividual|\yii\db\ActiveRecord
     */
    public function getIndividualPostback($flow_id = null, $wm_id = null)
    {
        $wm_id = isset($wm_id) ? $wm_id : null;

        return PostbackIndividual::find()
            ->select([
                'url',
                'url_approved',
                'url_cancelled',
            ])
            ->where(['wm_id' => $wm_id])
            ->andWhere(['flow_id' => $flow_id])
            ->asArray()
            ->one();
    }
}
