<?php

namespace common\models\webmaster;

use Yii;
use common\models\BaseModel;
use common\models\offer\Offer;
use common\modules\user\models\tables\User;

/**
 * This is the model class for table "{{%wm_offer}}".
 *
 * @property integer $wm_offer_id
 * @property integer $offer_id
 * @property integer $wm_id
 * @property integer $leads
 * @property integer $status
 * @property string $created_at
 *
 * @property WmCheckboxes[] $wmCheckboxes
 * @property Offer $offer
 * @property User $wm
 */
class WmOffer extends BaseModel
{
    public $updated_at = false;
    public $created_by = false;
    public $updated_by = false;

    const STATUS_NOT_TAKEN = 0;
    const STATUS_WAITING = 1;
    const STATUS_TAKEN = 2;
    const STATUS_REJECTED = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wm_offer}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['offer_id', 'wm_id', 'leads'], 'required'],
            [['offer_id', 'wm_id', 'leads', 'status'], 'integer'],
            [['created_at'], 'safe'],
            [['offer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Offer::className(), 'targetAttribute' => ['offer_id' => 'offer_id']],
            [['wm_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['wm_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'wm_offer_id' => Yii::t('app', 'Wm Offer ID'),
            'offer_id' => Yii::t('app', 'Offer ID'),
            'wm_id' => Yii::t('app', 'Wm ID'),
            'leads' => Yii::t('app', 'Leads'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function statuses($wm_offer_status = null)
    {
        $statuses = [
            self::STATUS_NOT_TAKEN => Yii::t('app', 'Not Taken'),
            self::STATUS_WAITING => Yii::t('app', 'Waiting'),
            self::STATUS_TAKEN => Yii::t('app', 'Taken'),
            self::STATUS_REJECTED => Yii::t('app', 'Rejected'),
        ];

        return isset($wm_offer_status) ? $statuses[$wm_offer_status] : $statuses;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWmCheckboxes()
    {
        return $this->hasMany(WmCheckboxes::className(), ['wm_offer_id' => 'wm_offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOffer()
    {
        return $this->hasOne(Offer::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWm()
    {
        return $this->hasOne(User::className(), ['id' => 'wm_id']);
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getWebmaster()
    {
        return self::find()
            ->select([
                'offer_id',
                'wm_id',
                'status'
            ])
            ->where([
                'wm_id' => Yii::$app->user->identity->getId(),
            ])
            ->asArray()
            ->all();
    }
}
