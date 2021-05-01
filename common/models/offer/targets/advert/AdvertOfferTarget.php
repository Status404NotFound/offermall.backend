<?php

namespace common\models\offer\targets\advert;

use common\helpers\FishHelper;
use common\models\geo\Geo;
use common\models\offer\Offer;
use common\models\offer\targets\wm\WmOfferTarget;
use common\modules\user\models\tables\User;
use Yii;
use common\models\BaseModel;

/**
 * This is the model class for table "advert_offer_target".
 *
 * @property integer $advert_offer_target_id
 * @property integer $offer_id
 * @property integer $advert_offer_target_status
 * @property integer $geo_id
 * @property integer $wm_visible
 * @property integer $active
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @property WmOfferTarget[] $wmOfferTargets
 * @property Geo $geo
 * @property Offer $offer
 * @property User $updatedBy
 * @property TargetAdvertGroup[] $targetAdvertGroups
 */
class AdvertOfferTarget extends BaseModel
{
    public $created_at = false;
    public $created_by = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'advert_offer_target';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['offer_id', 'advert_offer_target_status', 'geo_id'], 'required'],
            [['offer_id', 'advert_offer_target_status', 'geo_id', 'wm_visible', 'active', 'updated_by'], 'integer'],
            [['updated_at'], 'safe'],
            [['advert_offer_target_status', 'geo_id', 'offer_id'], 'unique', 'targetAttribute' => ['advert_offer_target_status', 'geo_id', 'offer_id'], 'message' => 'The combination of Offer ID, Advert Offer Target Status and Geo ID has already been taken.'],
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
            'advert_offer_target_id' => Yii::t('app', 'Advert Offer Target ID'),
            'offer_id' => Yii::t('app', 'Offer ID'),
            'advert_offer_target_status' => Yii::t('app', 'Advert Offer Target Status'),
            'geo_id' => Yii::t('app', 'Geo ID'),
            'wm_visible' => Yii::t('app', 'Wm Visible'),
            'active' => Yii::t('app', 'Active'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
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
    public function getWmOfferTargets()
    {
        return $this->hasMany(WmOfferTarget::className(), [
            'offer_id' => 'offer_id',
            'advert_offer_target_status' => 'advert_offer_target_status',
            'geo_id' => 'geo_id'
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetAdvertGroups()
    {
        return $this->hasMany(TargetAdvertGroup::className(), ['advert_offer_target_id' => 'advert_offer_target_id']);
    }

    /**
     * @inheritdoc
     * @return AdvertOfferTargetQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AdvertOfferTargetQuery(get_called_class());
    }

    /**
     * @param $offer_id
     * @param $wm_visible
     * @return array|AdvertOfferTarget[]
     */
    public static function getActiveStatusesByOfferId($offer_id, $wm_visible = false)
    {
        $statuses_query = self::find()
            ->select('advert_offer_target_status')
            ->distinct()
            ->where(['offer_id' => $offer_id]);
//            ->active();
        if ($wm_visible === true) $statuses_query->wm_visible();
        $statuses = $statuses_query->groupBy('advert_offer_target_status, advert_offer_target_id')
            ->asArray()
            ->all();
        return $statuses;
    }


    /**
     * @param $offer_id
     * @return array|AdvertOfferTarget[]
     */
    public static function getStatusesForSkuTabByOfferId($offer_id)
    {
        return self::find()
            ->select(['advert_offer_target_id', 'advert_offer_target_status', 'geo_id', 'active as is_active'])
            ->where(['offer_id' => $offer_id])
            ->asArray()
            ->all();
    }

    /**
     * @param $offer_id
     * @return array|AdvertOfferTarget[]
     */
    public static function getStatusesForBlockSkuTabByOfferId($offer_id)
    {
        $query = self::find()
            ->select(['advert_offer_target.advert_offer_target_id', 'advert_offer_target.advert_offer_target_status', 'advert_offer_target.geo_id'])
            ->leftJoin('target_advert_group', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->leftJoin('target_advert', 'target_advert.target_advert_group_id = target_advert_group.target_advert_group_id')
            ->where(['advert_offer_target.offer_id' => $offer_id])
            ->andWhere(['target_advert.active' => 1]);

        if (!is_null(Yii::$app->user->identity->getOwnerId())) $query->andWhere(['target_advert.advert_id' => Yii::$app->user->identity->getOwnerId()]);

        $result = $query
            ->asArray()
            ->all();

        return $result;
    }
}