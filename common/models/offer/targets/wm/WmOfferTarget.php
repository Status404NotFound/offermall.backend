<?php

namespace common\models\offer\targets\wm;

use common\models\geo\Geo;
use common\models\offer\Offer;
use common\models\BaseModel;
use common\models\offer\targets\advert\AdvertOfferTarget;
use common\modules\user\models\tables\User;
use Yii;

/**
 * This is the model class for table "wm_offer_target".
 *
 * @property integer $wm_offer_target_id
 * @property integer $target_id
 * @property integer $offer_id
 * @property integer $advert_offer_target_status
 *
 * @property integer $wm_offer_target_status
 * @property integer $geo_id
 *
 * @property string $updated_at
 * @property integer $updated_by
 * @property integer $active
 *
 * @property TargetWmGroup[] $targetWmGroups
 * @property AdvertOfferTarget $advertOfferTarget
 * @property User[] $wms
 * @property Geo $geo
 * @property Offer $offer
 * @property User $updatedBy
 */
class WmOfferTarget extends BaseModel
{
    public $created_at = false;
    public $created_by = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wm_offer_target';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['offer_id', 'advert_offer_target_status'], 'required'],
            [['offer_id', 'advert_offer_target_status', 'wm_offer_target_status', 'geo_id', 'updated_by', 'active'], 'integer'],
            [['updated_at'], 'safe'],
            [['advert_offer_target_status', 'wm_offer_target_status', 'geo_id', 'offer_id'], 'unique', 'targetAttribute' => ['advert_offer_target_status', 'wm_offer_target_status', 'geo_id', 'offer_id'], 'message' => 'The combination of Advert Offer Target Status, Wm Offer Target Status and Geo ID has already been taken.'],
            [['geo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Geo::className(), 'targetAttribute' => ['geo_id' => 'geo_id']],
            [['offer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Offer::className(), 'targetAttribute' => ['offer_id' => 'offer_id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'target_id' => Yii::t('app', 'Target ID'),
            'offer_id' => Yii::t('app', 'Offer ID'),
            'wm_offer_target_status' => Yii::t('app', 'Wm Offer Target Status'),
            'geo_id' => Yii::t('app', 'Geo ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'active' => Yii::t('app', 'Active'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetWmGroups()
    {
        return $this->hasMany(TargetWmGroup::className(), ['wm_offer_target_id' => 'wm_offer_target_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWms()
    {
        return $this->hasMany(User::className(), ['id' => 'wm_id'])->viaTable('target_wm', ['wm_offer_target_id' => 'wm_offer_target_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeo()
    {
        return $this->hasOne(Geo::className(), ['geo_id' => 'geo_id']);
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
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvertOfferTarget()
    {
        return $this->hasOne(AdvertOfferTarget::className(), [
            'advert_offer_target_status' => 'advert_offer_target_status',
            'geo_id' => 'geo_id',
            'offer_id' => 'offer_id'
        ]);
    }

    /**
     * @inheritdoc
     * @return WmOfferTargetQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new WmOfferTargetQuery(get_called_class());
    }
}