<?php

namespace common\models\offer;

use common\models\BaseModel;
use common\models\delivery\OrderDelivery;
use common\models\flow\Flow;
use common\models\landing\Landing;
use common\models\landing\OfferGeoPrice;
use common\models\LandingViews;
use common\models\offer\targets\advert\AdvertOfferTarget;
use common\models\offer\targets\wm\WmOfferTarget;
use common\models\onlinePayment\OnlinePayment;
use common\models\order\Order;
use common\models\order\OrderData;
use common\models\product\Product;
use common\models\webmaster\WmOffer;
use \common\modules\user\models\tables\User;
use crm\models\finstrip\DayOfferGeoSubCost;
use tds\modules\genform\tables\GenFormTable;
use Yii;

/**
 * This is the model class for table "offer".
 *
 * @property integer $offer_id
 * @property string $offer_hash
 * @property string $offer_name
 * @property integer $offer_status
 * @property integer $customer_send_sms
 * @property string $sms_text
 * @property string $description
 * @property resource $img
 * @property string $created_at
 * @property string $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property AdvertOfferTarget[] $advertOfferTargets
 * @property DayOfferGeoSubCost[] $dayOfferGeoSubCosts
 * @property Flow[] $flows
 * @property GenFormTable[] $genFormTables
 * @property Landing[] $landings
 * @property LandingViews[] $landingViews
 * @property User $createdBy
 * @property User $updatedBy
 * @property OfferGeoPrice[] $offerGeoPrices
 * @property OfferProduct[] $offerProducts
 * @property OfferTransit[] $offerTransits
 * @property OnlinePayment[] $onlinePayments
 * @property Order[] $orders
 * @property OrderData[] $orderData
 * @property OrderDelivery[] $orderDeliveries
 * @property WmOffer[] $wmOffers
 * @property WmOfferTarget[] $wmOfferTargets
 */
class Offer extends BaseModel
{
    const STATUS_ON_PAUSE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_ARCHIVED = 10;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'offer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['offer_name'], 'required'],
            [['offer_status', 'created_by', 'updated_by'], 'integer'],
            [['description', 'img'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['offer_hash'], 'string', 'max' => 32],
            [['offer_name'], 'string', 'max' => 255],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'offer_id' => Yii::t('app', 'Offer ID'),
            'offer_hash' => Yii::t('app', 'Offer Hash'),
            'offer_name' => Yii::t('app', 'Offer Name'),
            'offer_status' => Yii::t('app', 'Offer Status'),
            'description' => Yii::t('app', 'Description'),
            'img' => Yii::t('app', 'Img'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return array
     */
    public static function statusLabel()
    {
        return [
            self::STATUS_ACTIVE => Yii::t('app', 'Active'),
            self::STATUS_ON_PAUSE => Yii::t('app', 'On pause'),
            self::STATUS_ARCHIVED => Yii::t('app', 'Archived'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvertOfferTargets()
    {
        return $this->hasMany(AdvertOfferTarget::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDayOfferGeoSubCosts()
    {
        return $this->hasMany(DayOfferGeoSubCost::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFlows()
    {
        return $this->hasMany(Flow::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGenFormTables()
    {
        return $this->hasMany(GenFormTable::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLandings()
    {
        return $this->hasMany(Landing::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLandingViews()
    {
        return $this->hasMany(LandingViews::className(), ['offer_id' => 'offer_id']);
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
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOfferGeoPrices()
    {
        return $this->hasMany(OfferGeoPrice::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOfferProducts()
    {
        return $this->hasMany(OfferProduct::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOfferTransits()
    {
        return $this->hasMany(OfferTransit::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOnlinePayments()
    {
        return $this->hasMany(OnlinePayment::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderData()
    {
        return $this->hasMany(OrderData::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderDeliveries()
    {
        return $this->hasMany(OrderDelivery::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWmOffers()
    {
        return $this->hasMany(WmOffer::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWmOfferTargets()
    {
        return $this->hasMany(WmOfferTarget::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @inheritdoc
     * @return OfferQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OfferQuery(get_called_class());
    }

    public static function getAdvertGeo($offer_id)
    {
        $targets = AdvertOfferTarget::findAll(['offer_id' => $offer_id]);
        $geo = [];
        foreach ($targets as $target) {
            $geo[$target->geo_id] = $target->geo_id;
        }
        return $geo;
    }

    public static function getWmGeo($offer_id)
    {
        $targets = WmOfferTarget::findAll(['offer_id' => $offer_id]);
        $geo = [];
        foreach ($targets as $target) {
            $geo[$target->geo_id] = $target->geo_id;
        }
        return $geo;
    }

    public static function findProductIds($offer_id)
    {
        $product_id_array = [];
        $offer_products = OfferProduct::find()
            ->select('product_id')
            ->where(['offer_id' => $offer_id])
            ->asArray()->all();

        foreach ($offer_products as $offer_product) {
            $product_id_array[] = $offer_product['product_id'];
        }
        return $product_id_array;
    }

    public static function getWmOfferIdArray()
    {
        $wm_offer_id_array = [];
        $wm_offers = WmOfferTarget::find()
            ->select('offer_id')
            ->join('JOIN', 'target_wm_group', 'target_wm_group.wm_offer_target_id = wm_offer_target.wm_offer_target_id')
            ->join('JOIN', 'target_wm', 'target_wm.target_wm_group_id = target_wm_group.target_wm_group_id')
            ->where(['target_wm.wm_id' => Yii::$app->user->identity->getId()])
            ->asArray()->all();

        foreach ($wm_offers as $wm_offer) {
            $wm_offer_id_array[] = $wm_offer['offer_id'];
        }
        return $wm_offer_id_array;
    }

//    public static function findProductIds($offer_id)
//    {
//        $offer = self::find()->select('product_id')->where(['offer_id' => $offer_id])->asArray()->all();
//        return $offer['product_id'];
//    }
}