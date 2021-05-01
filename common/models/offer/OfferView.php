<?php

namespace common\models\offer;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "offer_view".
 *
 * @property integer $offer_id
 * @property string $offer_name
 * @property integer $offer_status
 * @property string $offer_hash
 * @property integer $geo_id
 * @property string $geo_name
 * @property string $geo_iso
 * @property integer $target_advert_id
 * @property integer $advert_id
 * @property integer $ta_active
 * @property string $advert_name
 * @property integer $target_advert_group_id
 * @property integer $advert_offer_target_id
 * @property integer $product_id
 * @property integer $send_sms_customer
 * @property integer $send_sms_owner
 * @property string $sms_text_customer
 * @property string $sms_text_owner
 * @property string $description
 * @property resource $img
 */
class OfferView extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'offer_view';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['offer_id', 'offer_status', 'geo_id', 'target_advert_id', 'advert_id', 'target_advert_group_id', 'advert_offer_target_id', 'product_id', 'send_sms_customer', 'send_sms_owner', 'ta_active'], 'integer'],
            [['offer_name', 'product_id'], 'required'],
            [['description', 'img'], 'string'],
            [['offer_name', 'geo_name', 'advert_name', 'sms_text_customer', 'sms_text_owner'], 'string', 'max' => 255],
            [['offer_hash'], 'string', 'max' => 32],
            [['geo_iso'], 'string', 'max' => 3],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'offer_id' => Yii::t('app', 'Offer ID'),
            'offer_name' => Yii::t('app', 'Offer Name'),
            'offer_status' => Yii::t('app', 'Offer Status'),
            'offer_hash' => Yii::t('app', 'Offer Hash'),
            'geo_id' => Yii::t('app', 'Geo ID'),
            'geo_name' => Yii::t('app', 'Geo Name'),
            'target_advert_id' => Yii::t('app', 'Target Advert ID'),
            'advert_id' => Yii::t('app', 'Advert ID'),
            'advert_name' => Yii::t('app', 'Advert Name'),
            'target_advert_group_id' => Yii::t('app', 'Target Advert Group ID'),
            'advert_offer_target_id' => Yii::t('app', 'Advert Offer Target ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'send_sms_customer' => Yii::t('app', 'Customer Send Sms'),
            'send_sms_owner' => Yii::t('app', 'Owner Send Sms'),
            'sms_text_customer' => Yii::t('app', 'Sms Text Customer'),
            'sms_text_owner' => Yii::t('app', 'Sms Text Owner'),
            'description' => Yii::t('app', 'Description'),
            'img' => Yii::t('app', 'Img'),
        ];
    }

    public static function getOfferGeoByOfferId($offer_id)
    {
        return self::find()->select(['geo_id', 'geo_name', 'geo_iso'])
            ->where(['offer_id' => $offer_id])
            ->groupBy('geo_id')
            ->asArray()
            ->all();
    }

    public static function getOfferAdvertsByOfferId($offer_id)
    {
        return self::find()->select(['advert_id', 'advert_name'])
            ->where(['offer_id' => $offer_id])
            ->groupBy('advert_id')
            ->asArray()->all();
    }
}