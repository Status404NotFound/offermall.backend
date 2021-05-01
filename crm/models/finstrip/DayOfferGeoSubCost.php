<?php

namespace crm\models\finstrip;

use common\models\BaseModel;
use common\models\finance\Currency;
use common\models\finance\KnownSubs;
use common\models\geo\Geo;
use common\models\offer\Offer;
use common\modules\user\models\tables\User;
use Yii;

/**
 * This is the model class for table "day_offer_geo_sub_cost".
 *
 * @property integer $id
 * @property integer $offer_id
 * @property integer $geo_id
 * @property integer $known_sub_id
 * @property string $date
 * @property string $sub_id_1
 * @property double $sum
 * @property integer $currency_id
 * @property double $rate
 * @property double $usd_sum
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $createdBy
 * @property Currency $currency
 * @property Geo $geo
 * @property KnownSubs $knownSub
 * @property Offer $offer
 * @property User $updatedBy
 */
class DayOfferGeoSubCost extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'day_offer_geo_sub_cost';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['offer_id', 'geo_id', 'known_sub_id', 'sum', 'currency_id', 'rate', 'usd_sum'], 'required'],
            [['offer_id', 'geo_id', 'known_sub_id', 'currency_id', 'created_by', 'updated_by'], 'integer'],
            [['date', 'created_at', 'updated_at'], 'safe'],
            [['sum', 'rate', 'usd_sum'], 'number'],
            [['sub_id_1'], 'string', 'max' => 255],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['currency_id'], 'exist', 'skipOnError' => true, 'targetClass' => Currency::className(), 'targetAttribute' => ['currency_id' => 'currency_id']],
            [['geo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Geo::className(), 'targetAttribute' => ['geo_id' => 'geo_id']],
            [['known_sub_id'], 'exist', 'skipOnError' => true, 'targetClass' => KnownSubs::className(), 'targetAttribute' => ['known_sub_id' => 'id']],
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
            'id' => Yii::t('app', 'ID'),
            'offer_id' => Yii::t('app', 'Offer ID'),
            'geo_id' => Yii::t('app', 'Geo ID'),
            'known_sub_id' => Yii::t('app', 'Known Sub ID'),
            'date' => Yii::t('app', 'Date'),
            'sub_id_1' => Yii::t('app', 'Sub Id 1'),
            'sum' => Yii::t('app', 'Sum'),
            'currency_id' => Yii::t('app', 'Currency ID'),
            'rate' => Yii::t('app', 'Rate'),
            'usd_sum' => Yii::t('app', 'Usd Sum'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['currency_id' => 'currency_id']);
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
    public function getKnownSub()
    {
        return $this->hasOne(KnownSubs::className(), ['id' => 'known_sub_id']);
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
}